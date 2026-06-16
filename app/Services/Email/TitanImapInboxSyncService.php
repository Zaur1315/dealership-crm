<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Models\DealershipEmailSetting;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\Lead;
use App\Services\Notifications\CrmNotificationService;
use Illuminate\Support\Facades\Storage;

class TitanImapInboxSyncService
{
    public function sync(DealershipEmailSetting $settings): int
    {
        if (! function_exists('imap_open')) {
            throw new \RuntimeException('PHP IMAP extension is not installed.');
        }

        if (! $settings->is_active) {
            return 0;
        }

        $this->ensureConfigured($settings);

        $mailbox = $this->mailboxString($settings);

        $connection = @imap_open(
            $mailbox,
            (string) $settings->imap_username,
            (string) $settings->imap_password,
        );

        if ($connection === false) {
            throw new \RuntimeException('Could not connect to Titan IMAP: '.$this->imapLastError());
        }

        try {
            $messageNumbers = imap_search($connection, 'ALL');

            if ($messageNumbers === false || $messageNumbers === []) {
                return 0;
            }

            rsort($messageNumbers);

            $synced = 0;

            foreach (array_slice($messageNumbers, 0, 100) as $messageNumber) {
                $overview = imap_fetch_overview($connection, (string) $messageNumber, 0)[0] ?? null;

                if ($overview === null) {
                    continue;
                }

                $providerMessageId = $this->providerMessageId($overview, (int) $messageNumber);

                $exists = Email::query()
                    ->where('dealership_id', $settings->dealership_id)
                    ->where('provider', 'titan_imap')
                    ->where('provider_message_id', $providerMessageId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $fromEmail = $this->extractEmail((string) ($overview->from ?? ''));
                $fromName = $this->extractName((string) ($overview->from ?? ''));

                $lead = $this->findLead($settings->dealership_id, $fromEmail);

                $email = Email::query()->create([
                    'dealership_id' => $settings->dealership_id,
                    'lead_id' => $lead?->id,
                    'is_matched_to_lead' => $lead instanceof Lead,
                    'needs_manual_review' => ! $lead instanceof Lead,
                    'created_by_user_id' => null,
                    'provider' => 'titan_imap',
                    'provider_message_id' => $providerMessageId,
                    'direction' => EmailDirection::INBOUND->value,
                    'status' => EmailStatus::ACTIVE->value,
                    'from_email' => $fromEmail ?: (string) ($overview->from ?? 'unknown@example.com'),
                    'from_name' => $fromName,
                    'to' => $settings->email_address !== null ? [$settings->email_address] : [],
                    'cc' => [],
                    'bcc' => [],
                    'subject' => $this->decodeMime((string) ($overview->subject ?? '')),
                    'body_text' => $this->bodyText($connection, (int) $messageNumber),
                    'body_html' => null,
                    'received_at' => isset($overview->date) ? now()->parse((string) $overview->date) : now(),
                ]);

                $this->syncAttachments(
                    connection: $connection,
                    messageNumber: (int) $messageNumber,
                    email: $email,
                );

                app(CrmNotificationService::class)->notifyNewEmail($email);

                $synced++;
            }

            return $synced;
        } finally {
            imap_close($connection);
        }
    }

    private function ensureConfigured(DealershipEmailSetting $settings): void
    {
        if (
            $settings->imap_host === null
            || $settings->imap_port === null
            || $settings->imap_username === null
            || $settings->imap_password === null
        ) {
            throw new \RuntimeException('Titan IMAP settings are incomplete.');
        }
    }

    private function mailboxString(DealershipEmailSetting $settings): string
    {
        $flags = '/imap';

        if ($settings->imap_encryption === 'ssl') {
            $flags .= '/ssl';
        }

        if ($settings->imap_encryption === 'tls') {
            $flags .= '/tls';
        }

        $flags .= '/novalidate-cert';

        return sprintf(
            '{%s:%d%s}INBOX',
            (string) $settings->imap_host,
            (int) $settings->imap_port,
            $flags,
        );
    }

    private function providerMessageId(object $overview, int $messageNumber): string
    {
        $messageId = $overview->message_id ?? null;

        if (is_string($messageId) && $messageId !== '') {
            return $messageId;
        }

        return 'imap-message-'.$messageNumber.'-'.md5(json_encode($overview) ?: (string) $messageNumber);
    }

    private function bodyText(mixed $connection, int $messageNumber): ?string
    {
        $body = imap_fetchbody($connection, $messageNumber, '1');

        if (! is_string($body) || $body === '') {
            $body = imap_body($connection, $messageNumber);
        }

        if (! is_string($body) || $body === '') {
            return null;
        }

        $decoded = quoted_printable_decode($body);

        return trim(strip_tags($decoded));
    }

    private function extractEmail(string $value): string
    {
        if (preg_match('/<([^>]+)>/', $value, $matches) === 1) {
            return trim($matches[1]);
        }

        return trim($value);
    }

    private function extractName(string $value): ?string
    {
        if (preg_match('/^(.+?)\s*</', $value, $matches) !== 1) {
            return null;
        }

        $name = trim($matches[1], " \t\n\r\0\x0B\"");

        return $name === '' ? null : $this->decodeMime($name);
    }

    private function decodeMime(string $value): string
    {
        $decoded = imap_mime_header_decode($value);

        if ($decoded === false || $decoded === []) {
            return $value;
        }

        return collect($decoded)
            ->map(fn (object $part): string => (string) $part->text)
            ->implode('');
    }

    private function findLead(int $dealershipId, string $email): ?Lead
    {
        if ($email === '') {
            return null;
        }

        return Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('email', $email)
            ->first();
    }

    private function imapLastError(): string
    {
        $error = imap_last_error();

        return is_string($error) ? $error : 'Unknown IMAP error';
    }

    private function syncAttachments(mixed $connection, int $messageNumber, Email $email): void
    {
        $structure = imap_fetchstructure($connection, $messageNumber);

        if (! is_object($structure) || ! isset($structure->parts) || ! is_array($structure->parts)) {
            return;
        }

        foreach ($structure->parts as $index => $part) {
            if (! is_object($part)) {
                continue;
            }

            $filename = $this->attachmentFilename($part);

            if ($filename === null) {
                continue;
            }

            $section = (string) ($index + 1);
            $content = imap_fetchbody($connection, $messageNumber, $section);

            if (! is_string($content) || $content === '') {
                continue;
            }

            $decoded = $this->decodeAttachmentContent($content, (int) ($part->encoding ?? 0));

            if ($decoded === '') {
                continue;
            }

            $path = 'email-attachments/inbound/'.$email->id.'/'.$filename;

            Storage::disk('local')->put($path, $decoded);

            EmailAttachment::query()->create([
                'email_id' => $email->id,
                'original_name' => $filename,
                'path' => $path,
                'mime_type' => $this->partMimeType($part),
                'size' => strlen($decoded),
            ]);
        }
    }

    private function attachmentFilename(object $part): ?string
    {
        foreach (['dparameters', 'parameters'] as $property) {
            if (! isset($part->{$property}) || ! is_array($part->{$property})) {
                continue;
            }

            foreach ($part->{$property} as $parameter) {
                if (! is_object($parameter)) {
                    continue;
                }

                $attribute = strtolower((string) ($parameter->attribute ?? ''));

                if (! in_array($attribute, ['filename', 'name'], true)) {
                    continue;
                }

                $value = (string) ($parameter->value ?? '');

                if ($value !== '') {
                    return $this->safeFilename($this->decodeMime($value));
                }
            }
        }

        return null;
    }

    private function decodeAttachmentContent(string $content, int $encoding): string
    {
        return match ($encoding) {
            3 => base64_decode($content, true) ?: '',
            4 => quoted_printable_decode($content),
            default => $content,
        };
    }

    private function partMimeType(object $part): ?string
    {
        $primary = match ((int) ($part->type ?? 0)) {
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other',
            default => null,
        };

        $subtype = strtolower((string) ($part->subtype ?? ''));

        if ($primary === null || $subtype === '') {
            return null;
        }

        return $primary.'/'.$subtype;
    }

    private function safeFilename(string $filename): string
    {
        $filename = trim($filename);

        if ($filename === '') {
            return 'attachment';
        }

        return preg_replace('/[^A-Za-z0-9._-]+/', '_', $filename) ?: 'attachment';
    }
}

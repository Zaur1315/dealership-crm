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
use Illuminate\Support\Carbon;
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
                $parsedBody = $this->parseMessageBody($connection, (int) $messageNumber);

                $payload = [
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
                    'body_text' => $parsedBody['body_text'],
                    'body_html' => $parsedBody['body_html'],
                    'received_at' => isset($overview->date) ? Carbon::parse((string) $overview->date) : now(),
                ];

                $email = Email::query()->create($this->sanitizePayload($payload));

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

    /**
     * @return array{body_text: ?string, body_html: ?string}
     */
    private function parseMessageBody(mixed $connection, int $messageNumber): array
    {
        $structure = imap_fetchstructure($connection, $messageNumber);

        $result = [
            'body_text' => null,
            'body_html' => null,
        ];

        if (! $structure instanceof \stdClass) {
            $body = imap_body($connection, $messageNumber);

            return [
                'body_text' => is_string($body) ? trim(strip_tags($this->sanitizeString($body))) : null,
                'body_html' => null,
            ];
        }

        $this->walkMessageParts(
            connection: $connection,
            messageNumber: $messageNumber,
            part: $structure,
            partNumber: '',
            result: $result,
        );

        if ($result['body_text'] === null && $result['body_html'] !== null) {
            $text = trim(strip_tags($result['body_html']));
            $result['body_text'] = $text === '' ? null : $text;
        }

        return $result;
    }

    /**
     * @param  array{body_text: ?string, body_html: ?string}  $result
     */
    private function walkMessageParts(
        mixed $connection,
        int $messageNumber,
        \stdClass $part,
        string $partNumber,
        array &$result,
    ): void {
        if (isset($part->parts) && is_array($part->parts)) {
            foreach ($part->parts as $index => $childPart) {
                if (! $childPart instanceof \stdClass) {
                    continue;
                }

                $childPartNumber = $partNumber === ''
                    ? (string) ($index + 1)
                    : $partNumber.'.'.($index + 1);

                $this->walkMessageParts(
                    connection: $connection,
                    messageNumber: $messageNumber,
                    part: $childPart,
                    partNumber: $childPartNumber,
                    result: $result,
                );
            }

            return;
        }

        if ($this->attachmentFilename($part) !== null) {
            return;
        }

        $mimeType = $this->partMimeType($part);

        if (! in_array($mimeType, ['text/plain', 'text/html'], true)) {
            return;
        }

        $body = $this->fetchDecodedPartBody(
            connection: $connection,
            messageNumber: $messageNumber,
            part: $part,
            partNumber: $partNumber,
        );

        if ($body === '') {
            return;
        }

        if ($mimeType === 'text/plain' && $result['body_text'] === null) {
            $result['body_text'] = trim($body);

            return;
        }

        if ($mimeType === 'text/html' && $result['body_html'] === null) {
            $result['body_html'] = trim($body);
        }
    }

    private function fetchDecodedPartBody(
        mixed $connection,
        int $messageNumber,
        \stdClass $part,
        string $partNumber,
    ): string {
        if ($partNumber === '') {
            $body = imap_body($connection, $messageNumber);
        } else {
            $body = imap_fetchbody($connection, $messageNumber, $partNumber);
        }

        if (! is_string($body) || $body === '') {
            return '';
        }

        $decoded = $this->decodePartContent($body, (int) ($part->encoding ?? 0));
        $charset = $this->partCharset($part);

        if ($charset !== null && strtoupper($charset) !== 'UTF-8') {
            $converted = @mb_convert_encoding($decoded, 'UTF-8', $charset);

            if (is_string($converted)) {
                $decoded = $converted;
            }
        }

        return $this->sanitizeString($decoded);
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
            return $this->sanitizeString($value);
        }

        return collect($decoded)
            ->map(function (object $part): string {
                $text = (string) ($part->text ?? '');
                $charset = strtoupper((string) ($part->charset ?? ''));

                if ($charset !== '' && $charset !== 'DEFAULT' && $charset !== 'UTF-8') {
                    $converted = @mb_convert_encoding($text, 'UTF-8', $charset);

                    if (is_string($converted)) {
                        $text = $converted;
                    }
                }

                return $this->sanitizeString($text);
            })
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

        if (! $structure instanceof \stdClass) {
            return;
        }

        $this->walkAttachmentParts(
            connection: $connection,
            messageNumber: $messageNumber,
            email: $email,
            part: $structure,
            partNumber: '',
        );
    }

    private function walkAttachmentParts(
        mixed $connection,
        int $messageNumber,
        Email $email,
        \stdClass $part,
        string $partNumber,
    ): void {
        if (isset($part->parts) && is_array($part->parts)) {
            foreach ($part->parts as $index => $childPart) {
                if (! $childPart instanceof \stdClass) {
                    continue;
                }

                $childPartNumber = $partNumber === ''
                    ? (string) ($index + 1)
                    : $partNumber.'.'.($index + 1);

                $this->walkAttachmentParts(
                    connection: $connection,
                    messageNumber: $messageNumber,
                    email: $email,
                    part: $childPart,
                    partNumber: $childPartNumber,
                );
            }

            return;
        }

        $filename = $this->attachmentFilename($part);

        if ($filename === null) {
            return;
        }

        $content = $partNumber === ''
            ? imap_body($connection, $messageNumber)
            : imap_fetchbody($connection, $messageNumber, $partNumber);

        if (! is_string($content) || $content === '') {
            return;
        }

        $decoded = $this->decodePartContent($content, (int) ($part->encoding ?? 0));

        if ($decoded === '') {
            return;
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

    private function decodePartContent(string $content, int $encoding): string
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

    private function partCharset(object $part): ?string
    {
        if (! isset($part->parameters) || ! is_array($part->parameters)) {
            return null;
        }

        foreach ($part->parameters as $parameter) {
            if (! is_object($parameter)) {
                continue;
            }

            if (strtolower((string) ($parameter->attribute ?? '')) === 'charset') {
                $value = (string) ($parameter->value ?? '');

                return $value === '' ? null : $value;
            }
        }

        return null;
    }

    private function safeFilename(string $filename): string
    {
        $filename = trim($filename);

        if ($filename === '') {
            return 'attachment';
        }

        return preg_replace('/[^A-Za-z0-9._-]+/', '_', $filename) ?: 'attachment';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            $payload[$key] = $this->sanitizeValue($value);
        }

        return $payload;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->sanitizeValue($item), $value);
        }

        return $value;
    }

    private function sanitizeString(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (! mb_check_encoding($value, 'UTF-8')) {
            $encoding = mb_detect_encoding($value, [
                'UTF-8',
                'Windows-1252',
                'ISO-8859-1',
                'ISO-8859-15',
            ], true);

            $value = mb_convert_encoding($value, 'UTF-8', $encoding ?: 'Windows-1252');
        }

        $cleaned = iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return $cleaned === false ? '' : $cleaned;
    }
}

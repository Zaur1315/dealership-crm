<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Data\Email\OutgoingEmailData;
use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Models\Dealership;
use App\Models\DealershipEmailSetting;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\Lead;
use App\Models\User;
use App\Services\Notifications\CrmNotificationService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class TitanSmtpEmailSender
{
    public function send(
        Dealership $dealership,
        OutgoingEmailData $data,
        ?Lead $lead = null,
        ?User $user = null,
    ): Email {
        $settings = $dealership->emailSetting;

        if (! $settings instanceof DealershipEmailSetting) {
            throw new \RuntimeException('Titan email settings are not configured.');
        }

        if (! $settings->is_active) {
            throw new \RuntimeException('Titan email settings are not active.');
        }

        $this->ensureConfigured($settings);

        $message = (new SymfonyEmail)
            ->from(new Address((string) $settings->from_email, (string) $settings->from_name))
            ->to(...$this->addresses($data->to))
            ->subject($data->subject);

        if ($data->cc !== []) {
            $message->cc(...$this->addresses($data->cc));
        }

        if ($data->bcc !== []) {
            $message->bcc(...$this->addresses($data->bcc));
        }

        if ($data->bodyHtml !== null) {
            $message->html($data->bodyHtml);
        }

        if ($data->bodyText !== null) {
            $message->text($data->bodyText);
        }

        if ($data->bodyHtml === null && $data->bodyText === null) {
            throw new \RuntimeException('Email body is required.');
        }

        foreach ($data->attachmentPaths as $path) {
            if (! Storage::disk('local')->exists($path)) {
                continue;
            }

            $message->attachFromPath(
                path: Storage::disk('local')->path($path),
                name: basename($path),
            );
        }

        $mailer = new Mailer($this->makeTransport($settings));
        $mailer->send($message);

        $email = Email::query()->create([
            'dealership_id' => $dealership->id,
            'lead_id' => $lead?->id,
            'created_by_user_id' => $user?->id,
            'provider' => 'titan_smtp',
            'provider_message_id' => $message->getHeaders()->get('Message-ID')?->getBodyAsString(),
            'direction' => EmailDirection::OUTBOUND->value,
            'status' => EmailStatus::ACTIVE->value,
            'from_email' => $settings->from_email,
            'from_name' => $settings->from_name,
            'to' => $data->to,
            'cc' => $data->cc,
            'bcc' => $data->bcc,
            'subject' => $data->subject,
            'body_text' => $data->bodyText,
            'body_html' => $data->bodyHtml,
            'sent_at' => now(),
        ]);

        foreach ($data->attachmentPaths as $path) {
            if (! Storage::disk('local')->exists($path)) {
                continue;
            }

            EmailAttachment::query()->create([
                'email_id' => $email->id,
                'original_name' => basename($path),
                'path' => $path,
                'mime_type' => Storage::disk('local')->mimeType($path),
                'size' => Storage::disk('local')->size($path),
            ]);
        }

        if ($this->shouldTriggerInvoiceAlert($email)) {
            app(CrmNotificationService::class)->notifyInvoiceAlert($email);
        }

        return $email;
    }

    private function makeTransport(DealershipEmailSetting $settings): TransportInterface
    {
        $scheme = $settings->smtp_encryption === 'ssl' ? 'smtps' : 'smtp';

        $username = rawurlencode((string) $settings->smtp_username);
        $password = rawurlencode((string) $settings->smtp_password);
        $host = (string) $settings->smtp_host;
        $port = (int) $settings->smtp_port;

        return Transport::fromDsn("{$scheme}://{$username}:{$password}@{$host}:{$port}");
    }

    /**
     * @param  array<int, string>  $emails
     * @return array<int, Address>
     */
    private function addresses(array $emails): array
    {
        return array_map(
            fn (string $email): Address => new Address($email),
            array_values(array_filter($emails, fn (string $email): bool => $email !== '')),
        );
    }

    private function ensureConfigured(DealershipEmailSetting $settings): void
    {
        if (
            $settings->from_email === null
            || $settings->from_name === null
            || $settings->smtp_host === null
            || $settings->smtp_port === null
            || $settings->smtp_username === null
            || $settings->smtp_password === null
        ) {
            throw new \RuntimeException('Titan SMTP settings are incomplete.');
        }
    }

    private function shouldTriggerInvoiceAlert(Email $email): bool
    {
        if (! $email->attachments()->exists()) {
            return false;
        }

        $haystack = strtolower(
            (string) $email->subject.' '.(string) $email->body_text.' '.(string) $email->body_html,
        );

        return str_contains($haystack, 'invoice');
    }
}

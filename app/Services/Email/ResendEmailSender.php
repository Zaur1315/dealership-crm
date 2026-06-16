<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Data\Email\OutgoingEmailData;
use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Models\Dealership;
use App\Models\DealershipEmailSetting;
use App\Models\Email;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ResendEmailSender
{
    public function send(
        Dealership $dealership,
        OutgoingEmailData $data,
        ?Lead $lead = null,
        ?User $user = null,
    ): Email {
        $settings = $dealership->emailSetting;

        if (! $settings instanceof DealershipEmailSetting) {
            throw new \RuntimeException('Resend email settings are not configured.');
        }

        if (! $settings->is_active || $settings->resend_api_key === null) {
            throw new \RuntimeException('Resend email settings are not configured.');
        }

        if ($settings->from_email === null || $settings->from_name === null) {
            throw new \RuntimeException('Sender email address is not configured.');
        }

        $payload = [
            'from' => $settings->from_name.' <'.$settings->from_email.'>',
            'to' => $data->to,
            'subject' => $data->subject,
        ];

        if ($data->bodyHtml !== null) {
            $payload['html'] = $data->bodyHtml;
        }

        if ($data->bodyText !== null) {
            $payload['text'] = $data->bodyText;
        }

        if ($data->cc !== []) {
            $payload['cc'] = $data->cc;
        }

        if ($data->bcc !== []) {
            $payload['bcc'] = $data->bcc;
        }

        $response = Http::withToken($settings->resend_api_key)
            ->acceptJson()
            ->post('https://api.resend.com/emails', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Resend email send failed: '.$response->body());
        }

        $responseData = $response->json();

        return Email::query()->create([
            'dealership_id' => $dealership->id,
            'lead_id' => $lead?->id,
            'created_by_user_id' => $user?->id,
            'provider' => 'resend',
            'provider_message_id' => is_array($responseData) ? ($responseData['id'] ?? null) : null,
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
    }
}

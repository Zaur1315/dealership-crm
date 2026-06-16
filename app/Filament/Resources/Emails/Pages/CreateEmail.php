<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Pages;

use App\Data\Email\OutgoingEmailData;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\Lead;
use App\Models\User;
use App\Services\Email\ResendEmailSender;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateEmail extends CreateRecord
{
    protected static string $resource = EmailResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        $user = Auth::user();

        $lead = null;

        if (($data['lead_id'] ?? null) !== null) {
            $lead = Lead::query()->find($data['lead_id']);
        }

        $email = app(ResendEmailSender::class)->send(
            dealership: $dealership,
            data: new OutgoingEmailData(
                to: [(string) $data['to']],
                subject: (string) $data['subject'],
                bodyText: (string) $data['body_text'],
            ),
            lead: $lead instanceof Lead ? $lead : null,
            user: $user instanceof User ? $user : null,
        );

        Notification::make()
            ->title('Email sent.')
            ->success()
            ->send();

        return $email;
    }

    protected function getRedirectUrl(): string
    {
        return EmailResource::getUrl('view', ['record' => $this->record]);
    }
}

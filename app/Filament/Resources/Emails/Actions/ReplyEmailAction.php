<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Actions;

use App\Data\Email\OutgoingEmailData;
use App\Enums\EmailStatus;
use App\Models\Dealership;
use App\Models\Email;
use App\Models\Lead;
use App\Models\User;
use App\Services\Email\TitanSmtpEmailSender;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ReplyEmailAction
{
    public static function make(): Action
    {
        return Action::make('reply')
            ->label('Reply')
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('primary')
            ->visible(fn (Email $record): bool => self::canReply($record))
            ->form([
                TextInput::make('to')
                    ->label('To')
                    ->email()
                    ->required()
                    ->default(fn (Email $record): string => $record->from_email),

                TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->default(fn (Email $record): string => self::replySubject($record)),

                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),
            ])
            ->action(function (Email $record, array $data): void {
                $dealership = Dealership::query()->find($record->dealership_id);

                if (! $dealership instanceof Dealership) {
                    throw new \RuntimeException('Dealership not found.');
                }

                $user = Auth::user();
                $lead = $record->lead;

                app(TitanSmtpEmailSender::class)->send(
                    dealership: $dealership,
                    data: new OutgoingEmailData(
                        to: [(string) $data['to']],
                        subject: (string) $data['subject'],
                        bodyText: (string) $data['body'],
                        bodyHtml: self::htmlBody((string) $data['body']),
                        cc: [],
                        bcc: [],
                    ),
                    lead: $lead instanceof Lead ? $lead : null,
                    user: $user instanceof User ? $user : null,
                );

                Notification::make()
                    ->title('Reply sent')
                    ->success()
                    ->send();
            });
    }

    private static function canReply(Email $email): bool
    {
        if ($email->from_email === '') {
            return false;
        }

        return $email->status !== EmailStatus::TRASHED->value;
    }

    private static function replySubject(Email $email): string
    {
        $subject = trim((string) $email->subject);

        if ($subject === '') {
            return 'Re:';
        }

        if (str_starts_with(strtolower($subject), 're:')) {
            return $subject;
        }

        return 'Re: '.$subject;
    }

    private static function htmlBody(string $body): string
    {
        return nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }
}

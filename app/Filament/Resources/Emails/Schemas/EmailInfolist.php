<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Schemas;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Models\Email;
use App\Models\EmailAttachment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Subject')
                            ->placeholder('(No subject)')
                            ->columnSpanFull(),

                        TextEntry::make('direction')
                            ->label('Direction')
                            ->badge()
                            ->formatStateUsing(fn (EmailDirection|string $state): string => $state instanceof EmailDirection ? $state->label() : $state),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (EmailStatus|string $state): string => $state instanceof EmailStatus ? $state->label() : $state),

                        TextEntry::make('from_email')
                            ->label('From')
                            ->placeholder('-'),

                        TextEntry::make('to')
                            ->label('To')
                            ->formatStateUsing(fn (mixed $state): string => self::listValue($state)),

                        TextEntry::make('cc')
                            ->label('CC')
                            ->formatStateUsing(fn (mixed $state): string => self::listValue($state)),

                        TextEntry::make('bcc')
                            ->label('BCC')
                            ->formatStateUsing(fn (mixed $state): string => self::listValue($state)),

                        TextEntry::make('lead.full_name')
                            ->label('Linked Lead')
                            ->placeholder('Unmatched'),

                        TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Message')
                    ->schema([
                        TextEntry::make('body_text')
                            ->label('Body')
                            ->placeholder('No plain text body')
                            ->columnSpanFull(),
                    ]),

                Section::make('Attachments')
                    ->schema([
                        TextEntry::make('attachments_list')
                            ->label('Files')
                            ->state(fn (Email $record): string => self::attachmentsHtml($record))
                            ->html()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function listValue(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '-';
        }

        if (is_string($state)) {
            $decoded = json_decode($state, true);

            if (is_array($decoded)) {
                return self::listValue($decoded);
            }

            return $state;
        }

        if (! is_array($state)) {
            return is_scalar($state) ? (string) $state : '-';
        }

        $values = [];

        foreach ($state as $value) {
            if (is_scalar($value) && (string) $value !== '') {
                $values[] = (string) $value;
            }
        }

        return $values === [] ? '-' : implode(', ', $values);
    }

    private static function attachmentsHtml(Email $email): string
    {
        $attachments = $email->attachments;

        if ($attachments->isEmpty()) {
            return '-';
        }

        $links = [];

        foreach ($attachments as $attachment) {
            if (! $attachment instanceof EmailAttachment) {
                continue;
            }

            $url = route('email-attachments.download', $attachment);

            $links[] = sprintf(
                '<a href="%s" class="text-primary-600 hover:underline">%s</a>',
                e($url),
                e($attachment->original_name),
            );
        }

        return $links === [] ? '-' : implode('<br>', $links);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Enums\AuditLogAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Event')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime(),

                        TextEntry::make('action')
                            ->label('Action')
                            ->badge()
                            ->formatStateUsing(
                                fn (AuditLogAction|string $state): string => $state instanceof AuditLogAction
                                    ? $state->label()
                                    : (AuditLogAction::tryFrom($state)?->label() ?? $state),
                            )
                            ->color(
                                fn (AuditLogAction|string $state): string => match ($state instanceof AuditLogAction ? $state : AuditLogAction::tryFrom($state)) {
                                    AuditLogAction::CREATED => 'success',
                                    AuditLogAction::UPDATED => 'warning',
                                    AuditLogAction::DELETED => 'danger',
                                    default => 'gray',
                                },
                            ),
                    ])
                    ->columns(3),

                Section::make('Entity')
                    ->schema([
                        TextEntry::make('entity_type')
                            ->label('Entity Type')
                            ->formatStateUsing(fn (?string $state): string => $state === null ? '-' : class_basename($state)),

                        TextEntry::make('entity_id')
                            ->label('Entity ID')
                            ->placeholder('-'),

                        TextEntry::make('entity_label')
                            ->label('Entity Label')
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make('User')
                    ->schema([
                        TextEntry::make('user_name')
                            ->label('User')
                            ->placeholder('System'),

                        TextEntry::make('user_id')
                            ->label('User ID')
                            ->placeholder('-'),

                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->placeholder('-'),

                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Changes')
                    ->schema([
                        TextEntry::make('old_values')
                            ->label('Old Values')
                            ->formatStateUsing(fn (?array $state): string => self::jsonValue($state))
                            ->columnSpanFull(),

                        TextEntry::make('new_values')
                            ->label('New Values')
                            ->formatStateUsing(fn (?array $state): string => self::jsonValue($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function jsonValue(?array $state): string
    {
        if ($state === null || $state === []) {
            return '-';
        }

        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json === false ? '-' : $json;
    }
}

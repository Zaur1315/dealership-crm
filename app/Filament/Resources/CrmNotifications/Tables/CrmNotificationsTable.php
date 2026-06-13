<?php

namespace App\Filament\Resources\CrmNotifications\Tables;

use App\Enums\CrmNotificationType;
use App\Models\CrmNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CrmNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (CrmNotification $record): string => $record->read_at === null ? 'bold' : 'regular'),

                TextColumn::make('body')
                    ->label('Message')
                    ->limit(60)
                    ->placeholder('No message'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (CrmNotificationType|string $state): string => $state instanceof CrmNotificationType ? $state->label() : $state),

                TextColumn::make('read_at')
                    ->label('Read')
                    ->dateTime()
                    ->placeholder('Unread')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (CrmNotification $record): string => route('crm-notifications.open', $record))
                    ->visible(fn (CrmNotification $record): bool => $record->target_url !== null),

                Action::make('mark_read')
                    ->label('Mark as read')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (CrmNotification $record): bool => $record->read_at === null)
                    ->action(function (CrmNotification $record): void {
                        $record->markAsRead();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('mark_read')
                    ->label('Mark as read')
                    ->icon('heroicon-o-check')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            if ($record instanceof CrmNotification) {
                                $record->markAsRead();
                            }
                        }
                    }),
            ]);
    }
}

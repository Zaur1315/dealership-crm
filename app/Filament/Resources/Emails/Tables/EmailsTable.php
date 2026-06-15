<?php

namespace App\Filament\Resources\Emails\Tables;

use App\Enums\EmailStatus;
use App\Models\Email;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('from_email')
                    ->label('From')
                    ->searchable(),

                TextColumn::make('to')
                    ->label('To')
                    ->state(fn (Email $record): string => implode(', ', $record->to ?? []))
                    ->searchable(),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(60)
                    ->placeholder('(No subject)'),

                TextColumn::make('lead.full_name')
                    ->label('Lead')
                    ->placeholder('Unmatched'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('move_to_trash')
                    ->label('Move to Trash')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->visible(fn (Email $record): bool => $record->getAttribute('status') === EmailStatus::ACTIVE->value)
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::TRASHED->value,
                            'trashed_at' => now(),
                        ])->save();
                    }),

                Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->visible(fn (Email $record): bool => $record->getAttribute('status') === EmailStatus::TRASHED->value)
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::HIDDEN->value,
                            'hidden_at' => now(),
                        ])->save();
                    }),

                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Email $record): bool => in_array($record->getAttribute('status'), [
                        EmailStatus::TRASHED->value,
                        EmailStatus::HIDDEN->value,
                    ], true))
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::ACTIVE->value,
                            'trashed_at' => null,
                            'hidden_at' => null,
                        ])->save();
                    }),

                Action::make('delete_permanently')
                    ->label('Delete Permanently')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(function (Email $record): bool {
                        $user = Auth::user();

                        return $user instanceof User
                            && $user->isGm()
                            && $record->getAttribute('status') === EmailStatus::HIDDEN->value;
                    })
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::DELETED->value,
                            'deleted_at' => now(),
                        ])->save();
                    }), ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

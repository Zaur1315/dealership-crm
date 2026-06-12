<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\Security\PasswordRules;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_GM => 'Owner / GM',
                        User::ROLE_MANAGER => 'Manager',
                        User::ROLE_SALESPERSON => 'Salesperson',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        User::STATUS_ACTIVE => 'success',
                        User::STATUS_LOCKED => 'danger',
                        User::STATUS_DEACTIVATED => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('dealerships.name')
                    ->label('Dealerships')
                    ->badge()
                    ->separator(',')
                    ->placeholder('No dealerships'),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => auth()->id() !== $record->id)
                    ->requiresConfirmation(),

                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(PasswordRules::default()),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->forceFill([
                            'password' => $data['password'],
                            'failed_login_attempts' => 0,
                            'locked_at' => null,
                            'status' => User::STATUS_ACTIVE,
                        ])->save();

                        Notification::make()
                            ->title('Password reset successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (User $record): bool => auth()->id() !== $record->id && ! $record->isLocked())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'status' => User::STATUS_LOCKED,
                            'locked_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('User locked.')
                            ->success()
                            ->send();
                    }),

                Action::make('unlock')
                    ->label('Unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isLocked())
                    ->schema([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(PasswordRules::default()),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->forceFill([
                            'password' => $data['password'],
                            'status' => User::STATUS_ACTIVE,
                            'failed_login_attempts' => 0,
                            'locked_at' => null,
                        ])->save();

                        Notification::make()
                            ->title('User unlocked successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-user-minus')
                    ->color('warning')
                    ->visible(fn (User $record): bool => auth()->id() !== $record->id && ! $record->isDeactivated())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'status' => User::STATUS_DEACTIVATED,
                        ])->save();

                        Notification::make()
                            ->title('User deactivated.')
                            ->success()
                            ->send();
                    }),

                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isDeactivated())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'status' => User::STATUS_ACTIVE,
                        ])->save();

                        Notification::make()
                            ->title('User reactivated.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('dealerships')->latest('id'));
    }
}

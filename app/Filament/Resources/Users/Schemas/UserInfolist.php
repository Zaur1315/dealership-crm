<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Profile')
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Full Name'),

                        TextEntry::make('username')
                            ->label('Username'),

                        TextEntry::make('role')
                            ->label('Role')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                User::ROLE_GM => 'Owner / GM',
                                User::ROLE_MANAGER => 'Manager',
                                User::ROLE_SALESPERSON => 'Salesperson',
                                default => $state,
                            }),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('telegram_contact')
                            ->label('Telegram')
                            ->placeholder('Not configured'),

                        TextEntry::make('dealerships.name')
                            ->label('Assigned Dealerships')
                            ->badge()
                            ->separator(','),
                    ])
                    ->columns(2),

                Section::make('Login Information')
                    ->schema([
                        TextEntry::make('last_login_at')
                            ->label('Last Login')
                            ->dateTime()
                            ->placeholder('Never'),

                        TextEntry::make('failed_login_attempts')
                            ->label('Failed Login Attempts'),

                        TextEntry::make('locked_at')
                            ->label('Locked At')
                            ->dateTime()
                            ->placeholder('Not locked'),
                    ])
                    ->columns(3),
            ]);
    }
}

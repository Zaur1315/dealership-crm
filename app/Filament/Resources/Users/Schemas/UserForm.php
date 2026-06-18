<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Security\PasswordRules;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(180)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),

                        Select::make('role')
                            ->label('Role')
                            ->required()
                            ->options([
                                User::ROLE_GM => 'Owner / GM',
                                User::ROLE_MANAGER => 'Manager',
                                User::ROLE_SALESPERSON => 'Salesperson',
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default(User::STATUS_ACTIVE)
                            ->options([
                                User::STATUS_ACTIVE => 'Active',
                                User::STATUS_LOCKED => 'Locked',
                                User::STATUS_DEACTIVATED => 'Deactivated',
                            ]),

                        TextInput::make('telegram_contact')
                            ->label('Telegram Contact')
                            ->placeholder('@username')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Password')
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(PasswordRules::default())
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => $state)
                            ->helperText(
                                'Required when creating a user. Leave empty when editing to keep the current password.'
                            ),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation, callable $get): bool => $operation === 'create' || filled($get('password')))
                            ->same('password')
                            ->dehydrated(false),
                    ]),

                Section::make('Dealership Assignments')
                    ->schema([
                        CheckboxList::make('dealerships')
                            ->label('Assigned Dealerships')
                            ->relationship('dealerships', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2),
                    ]),
            ]);
    }
}

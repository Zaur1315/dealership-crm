<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipEmailSettings\Schemas;

use App\Enums\EmailSetupStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DealershipEmailSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dealership_id')
                    ->label('Dealership')
                    ->relationship('dealership', 'name')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('domain')
                    ->label('Domain')
                    ->placeholder('example.com'),

                TextInput::make('email_address')
                    ->label('Email Address')
                    ->email()
                    ->placeholder('sales@example.com')
                    ->required(),

                TextInput::make('from_email')
                    ->label('From Email')
                    ->email()
                    ->placeholder('sales@example.com')
                    ->required(),

                TextInput::make('from_name')
                    ->label('From Name')
                    ->placeholder('Dealership Sales')
                    ->required(),

                TextInput::make('imap_host')
                    ->label('IMAP Host')
                    ->default('imap.titan.email')
                    ->required(),

                TextInput::make('imap_port')
                    ->label('IMAP Port')
                    ->numeric()
                    ->default(993)
                    ->required(),

                Select::make('imap_encryption')
                    ->label('IMAP Encryption')
                    ->options([
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'none' => 'None',
                    ])
                    ->default('ssl')
                    ->required(),

                TextInput::make('imap_username')
                    ->label('IMAP Username')
                    ->placeholder('sales@example.com')
                    ->required(),

                TextInput::make('imap_password')
                    ->label('IMAP Password')
                    ->password()
                    ->revealable(),

                TextInput::make('smtp_host')
                    ->label('SMTP Host')
                    ->default('smtp.titan.email')
                    ->required(),

                TextInput::make('smtp_port')
                    ->label('SMTP Port')
                    ->numeric()
                    ->default(465)
                    ->required(),

                Select::make('smtp_encryption')
                    ->label('SMTP Encryption')
                    ->options([
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                        'none' => 'None',
                    ])
                    ->default('ssl')
                    ->required(),

                TextInput::make('smtp_username')
                    ->label('SMTP Username')
                    ->placeholder('sales@example.com')
                    ->required(),

                TextInput::make('smtp_password')
                    ->label('SMTP Password')
                    ->password()
                    ->revealable(),

                Select::make('dns_status')
                    ->label('DNS Status')
                    ->options(EmailSetupStatus::options())
                    ->required()
                    ->default(EmailSetupStatus::NOT_CONFIGURED->value),

                Select::make('mailbox_status')
                    ->label('Mailbox Status')
                    ->options(EmailSetupStatus::options())
                    ->required()
                    ->default(EmailSetupStatus::NOT_CONFIGURED->value),

                Select::make('sending_status')
                    ->label('Sending Status')
                    ->options(EmailSetupStatus::options())
                    ->required()
                    ->default(EmailSetupStatus::NOT_CONFIGURED->value),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}

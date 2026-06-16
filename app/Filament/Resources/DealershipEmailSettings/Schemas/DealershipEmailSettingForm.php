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

                TextInput::make('from_email')
                    ->label('From Email')
                    ->email()
                    ->placeholder('sales@example.com'),

                TextInput::make('from_name')
                    ->label('From Name')
                    ->placeholder('Dealership Sales'),

                TextInput::make('resend_api_key')
                    ->label('Resend API Key')
                    ->password()
                    ->revealable(),

                TextInput::make('resend_webhook_secret')
                    ->label('Resend Webhook Secret')
                    ->password()
                    ->revealable(),

                TextInput::make('titan_email')
                    ->label('Titan Email')
                    ->email(),

                TextInput::make('titan_account_reference')
                    ->label('Titan Account Reference'),

                TextInput::make('titan_api_key')
                    ->label('Titan API Key')
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

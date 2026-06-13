<?php

namespace App\Filament\Resources\CrmNotifications\Schemas;

use App\Enums\CrmNotificationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CrmNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dealership_id')
                    ->relationship('dealership', 'name'),
                TextInput::make('recipient_user_id')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(CrmNotificationType::class)
                    ->default('new_lead')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('body')
                    ->columnSpanFull(),
                TextInput::make('target_type'),
                TextInput::make('target_id')
                    ->numeric(),
                TextInput::make('target_url')
                    ->url(),
                TextInput::make('payload'),
                DateTimePicker::make('read_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }
}

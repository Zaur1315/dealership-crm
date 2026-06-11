<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dealerships\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DealershipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Dealership Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('email')
                    ->label('Shared Inbox Email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }
}

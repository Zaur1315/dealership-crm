<?php

namespace App\Filament\Resources\CrmNotifications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CrmNotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('dealership.name')
                    ->label('Dealership')
                    ->placeholder('-'),
                TextEntry::make('recipient_user_id')
                    ->numeric(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('title'),
                TextEntry::make('body')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('target_type')
                    ->placeholder('-'),
                TextEntry::make('target_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('target_url')
                    ->placeholder('-'),
                TextEntry::make('read_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

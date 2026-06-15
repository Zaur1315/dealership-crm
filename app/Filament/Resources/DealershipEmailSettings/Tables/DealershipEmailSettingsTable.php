<?php

namespace App\Filament\Resources\DealershipEmailSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DealershipEmailSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dealership.name')
                    ->label('Dealership')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('from_email')
                    ->label('From Email')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('dns_status')
                    ->label('DNS')
                    ->badge(),

                TextColumn::make('mailbox_status')
                    ->label('Mailbox')
                    ->badge(),

                TextColumn::make('sending_status')
                    ->label('Sending')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

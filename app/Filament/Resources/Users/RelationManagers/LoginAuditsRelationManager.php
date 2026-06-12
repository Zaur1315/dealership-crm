<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoginAuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'loginAudits';

    protected static ?string $title = 'Login Audit Trail';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('logged_in_at')
            ->columns([
                TextColumn::make('logged_in_at')
                    ->label('Logged In At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),

                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(80)
                    ->tooltip(fn ($record): ?string => $record->user_agent),
            ])
            ->defaultSort('logged_in_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('logged_in_at'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

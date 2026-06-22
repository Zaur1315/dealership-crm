<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Enums\LeadActivityType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity Timeline';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(
                        fn (LeadActivityType|string $state): string => $state instanceof LeadActivityType
                            ? $state->label()
                            : (LeadActivityType::tryFrom($state)?->label() ?? $state),
                    )
                    ->color(
                        fn (LeadActivityType|string $state): string => match ($state instanceof LeadActivityType ? $state : LeadActivityType::tryFrom($state)) {
                            LeadActivityType::LEAD_CREATED => 'success',
                            LeadActivityType::COMMENT_CREATED => 'info',
                            LeadActivityType::TASK_CREATED => 'gray',
                            LeadActivityType::TASK_COMPLETED => 'success',
                            LeadActivityType::TASK_EXPIRED => 'danger',
                            LeadActivityType::STAGE_CHANGED => 'warning',
                            LeadActivityType::TASK_UPDATED => 'info',
                            default => 'gray',
                        },
                    ),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('user_name')
                    ->label('User')
                    ->placeholder('System'),

                TextColumn::make('old_values')
                    ->label('Old')
                    ->formatStateUsing(fn (?array $state): string => self::jsonValue($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('new_values')
                    ->label('New')
                    ->formatStateUsing(fn (?array $state): string => self::jsonValue($state))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(LeadActivityType::options()),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'))
            ->paginated([10, 25, 50])
            ->recordActions([]);
    }

    private static function jsonValue(?array $state): string
    {
        if ($state === null) {
            return '-';
        }

        $json = json_encode($state, JSON_UNESCAPED_SLASHES);

        return $json === false ? '-' : $json;
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}

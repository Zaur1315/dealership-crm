<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Enums\AuditLogAction;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->formatStateUsing(
                        fn (AuditLogAction|string $state): string => $state instanceof AuditLogAction
                            ? $state->label()
                            : (AuditLogAction::tryFrom($state)?->label() ?? $state),
                    )
                    ->color(
                        fn (AuditLogAction|string $state): string => match ($state instanceof AuditLogAction ? $state : AuditLogAction::tryFrom($state)) {
                            AuditLogAction::CREATED => 'success',
                            AuditLogAction::UPDATED => 'warning',
                            AuditLogAction::DELETED => 'danger',
                            default => 'gray',
                        },
                    ),

                TextColumn::make('entity_type')
                    ->label('Entity')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->searchable(),

                TextColumn::make('entity_label')
                    ->label('Record')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('changed_fields')
                    ->label('Changed Fields')
                    ->state(function (AuditLog $record): string {
                        $values = $record->getAttribute('new_values');

                        if (! is_array($values) || $values === []) {
                            return '-';
                        }

                        return implode(', ', array_keys($values));
                    })
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),

                TextColumn::make('user_name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->options(AuditLogAction::options()),

                SelectFilter::make('entity_type')
                    ->label('Entity')
                    ->options(fn (): array => AuditLog::query()
                        ->whereNotNull('entity_type')
                        ->distinct()
                        ->orderBy('entity_type')
                        ->pluck('entity_type', 'entity_type')
                        ->mapWithKeys(fn (string $value, string $key): array => [$key => class_basename($value)])
                        ->all()),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn (): array => User::query()
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->all()),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Created From'),

                        DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'));
    }
}

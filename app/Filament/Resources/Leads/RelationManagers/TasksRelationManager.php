<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    protected static ?string $modelLabel = 'Task';

    protected static ?string $pluralModelLabel = 'Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Lead Tasks')
            ->description('Tasks connected to this lead.')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => self::formatState($state))
                    ->color(fn (mixed $state): string => match (self::stateValue($state)) {
                        'phone_call' => 'info',
                        'email' => 'warning',
                        'general' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => self::formatState($state))
                    ->color(fn (mixed $state): string => match (self::stateValue($state)) {
                        'active' => 'info',
                        'completed' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('createdBy.full_name')
                    ->label('Created by')
                    ->placeholder('System')
                    ->toggleable(),

                TextColumn::make('completedBy.full_name')
                    ->label('Completed by')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Task $record): string => TaskResource::getUrl('view', [
                        'record' => $record,
                    ])),

                EditAction::make()
                    ->visible(fn (Task $record): bool => self::canManageTask($record)),
            ])
            ->modifyQueryUsing(fn ($query) => $query->latest('id'));
    }

    private static function canManageTask(Task $task): bool
    {
        $user = Auth::user();

        return $user instanceof User;
    }

    private static function formatState(mixed $state): string
    {
        return str(self::stateValue($state))
            ->replace('_', ' ')
            ->headline()
            ->toString();
    }

    private static function stateValue(mixed $state): string
    {
        if ($state instanceof \BackedEnum) {
            return (string) $state->value;
        }

        return (string) $state;
    }
}

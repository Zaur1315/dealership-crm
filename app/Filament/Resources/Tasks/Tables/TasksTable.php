<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Tables;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Task;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(
                        fn (TaskType|string $state): string => $state instanceof TaskType ? $state->label() : $state
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (TaskStatus|string $state): string => $state instanceof TaskStatus ? $state->label() : $state
                    )
                    ->color(
                        fn (TaskStatus|string $state
                        ): string => match ($state instanceof TaskStatus ? $state : TaskStatus::tryFrom($state)) {
                            TaskStatus::ACTIVE => 'warning',
                            TaskStatus::COMPLETED => 'success',
                            TaskStatus::EXPIRED => 'danger',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('lead.full_name')
                    ->label('Lead')
                    ->placeholder('Manual task')
                    ->url(fn (Task $record): ?string => $record->lead_id !== null
                        ? LeadResource::getUrl('view', ['record' => $record->lead_id])
                        : null),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No due date'),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not completed'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordClasses(fn (Task $record): string => $record->isExpired()
                ? 'bg-danger-50 dark:bg-danger-950'
                : '')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(TaskType::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Task $record): bool => ! $record->isCompleted())
                    ->action(function (Task $record): void {
                        $user = Auth::user();

                        if ($record->isEmailTask()) {
                            Notification::make()
                                ->title('Email task verification is not available yet.')
                                ->body(
                                    'Email tasks will require an outgoing email after the Email module is implemented.'
                                )
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->forceFill([
                            'status' => TaskStatus::COMPLETED,
                            'completed_at' => now(),
                            'completed_by_user_id' => $user instanceof User ? $user->id : null,
                        ])->save();

                        Notification::make()
                            ->title('Task completed.')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'));
    }
}

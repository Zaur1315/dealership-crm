<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description'),

                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(
                                fn (TaskType|string $state): string => $state instanceof TaskType ? $state->label(
                                ) : $state
                            ),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (TaskStatus|string $state): string => $state instanceof TaskStatus ? $state->label(
                                ) : $state
                            ),

                        TextEntry::make('lead.full_name')
                            ->label('Linked Lead')
                            ->placeholder('Manual task'),

                        TextEntry::make('createdBy.full_name')
                            ->label('Created By')
                            ->placeholder('System / Deleted User'),

                        TextEntry::make('completedBy.full_name')
                            ->label('Completed By')
                            ->placeholder('Not completed'),

                        TextEntry::make('due_at')
                            ->label('Due At')
                            ->dateTime()
                            ->placeholder('No due date'),

                        TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed'),

                        TextEntry::make('expired_at')
                            ->label('Expired At')
                            ->dateTime()
                            ->placeholder('Not expired'),
                    ])
                    ->columns(2),
            ]);
    }
}

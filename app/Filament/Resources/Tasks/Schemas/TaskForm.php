<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Lead;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Type')
                            ->required()
                            ->options(TaskType::options())
                            ->default(TaskType::GENERAL->value),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(TaskStatus::options())
                            ->default(TaskStatus::ACTIVE->value),

                        DateTimePicker::make('due_at')
                            ->label('Due At')
                            ->seconds(false)
                            ->nullable(),

                        Select::make('lead_id')
                            ->label('Linked Lead')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->options(function (): array {
                                $dealership = app(CurrentDealershipContext::class)->ensureSelected();

                                return Lead::query()
                                    ->where('dealership_id', $dealership->id)
                                    ->orderBy('full_name')
                                    ->pluck('full_name', 'id')
                                    ->all();
                            }),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}

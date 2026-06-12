<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadPipelineStage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->required()
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(1000)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Deal Information')
                    ->schema([
                        Select::make('pipeline_stage')
                            ->label('Pipeline Stage')
                            ->required()
                            ->options(LeadPipelineStage::options())
                            ->default(LeadPipelineStage::NEW->value),

                        TextInput::make('deal_value')
                            ->label('Deal Value')
                            ->numeric()
                            ->prefix('$')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}

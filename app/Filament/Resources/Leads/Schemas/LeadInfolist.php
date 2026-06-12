<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadPipelineStage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Full Name'),

                        TextEntry::make('phone_number')
                            ->label('Phone Number'),

                        TextEntry::make('email')
                            ->label('Email Address'),

                        TextEntry::make('address')
                            ->label('Address')
                            ->placeholder('Not provided'),
                    ])
                    ->columns(2),

                Section::make('Deal Information')
                    ->schema([
                        TextEntry::make('pipeline_stage')
                            ->label('Pipeline Stage')
                            ->badge()
                            ->formatStateUsing(
                                fn (LeadPipelineStage|string $state
                                ): string => $state instanceof LeadPipelineStage ? $state->label() : $state
                            ),

                        TextEntry::make('deal_value')
                            ->label('Deal Value')
                            ->money('USD')
                            ->placeholder('$0.00'),

                        TextEntry::make('createdBy.full_name')
                            ->label('Created By')
                            ->placeholder('Deleted User'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Schemas;

use App\Models\Lead;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->label('Lead')
                    ->options(fn (): array => Lead::query()
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('to')
                    ->label('To')
                    ->email()
                    ->required(),

                TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255),

                Textarea::make('body_text')
                    ->label('Message')
                    ->required()
                    ->rows(10),

                FileUpload::make('attachments')
                    ->label('Attachments')
                    ->multiple()
                    ->disk('local')
                    ->directory('email-attachments')
                    ->preserveFilenames(),
            ]);
    }
}

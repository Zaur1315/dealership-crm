<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\Email;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'emails';

    protected static ?string $title = 'Email Thread';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge()
                    ->formatStateUsing(fn (EmailDirection|string $state): string => $state instanceof EmailDirection ? $state->label() : $state),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (EmailStatus|string $state): string => $state instanceof EmailStatus ? $state->label() : $state),

                TextColumn::make('from_email')
                    ->label('From')
                    ->searchable(),

                TextColumn::make('to')
                    ->label('To')
                    ->state(fn (Email $record): string => implode(', ', $record->to ?? [])),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(60)
                    ->placeholder('(No subject)'),

                IconColumn::make('needs_manual_review')
                    ->label('Review')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Email $record): string => EmailResource::getUrl('view', ['record' => $record])),

                Action::make('unlink')
                    ->label('Unlink')
                    ->icon('heroicon-o-link-slash')
                    ->color('warning')
                    ->visible(fn (): bool => Auth::user() instanceof User)
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'lead_id' => null,
                            'is_matched_to_lead' => false,
                            'needs_manual_review' => true,
                        ])->save();
                    }),
            ]);
    }
}

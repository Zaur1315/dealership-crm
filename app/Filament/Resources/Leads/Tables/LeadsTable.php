<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadPipelineStage;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadPipelineService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('pipeline_stage')
                    ->label('Stage')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => self::stageLabel($state))
                    ->color(fn (mixed $state): string => self::stageColor($state)),

                TextColumn::make('createdBy.full_name')
                    ->label('Created by')
                    ->placeholder('Unknown')
                    ->toggleable(),

                TextColumn::make('deal_value')
                    ->label('Deal Value')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('$0.00'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('pipeline_stage')
                    ->label('Pipeline Stage')
                    ->options(LeadPipelineStage::options()),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),

                Action::make('change_stage')
                    ->label('Change Stage')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Select::make('pipeline_stage')
                            ->label('Pipeline Stage')
                            ->options(LeadPipelineStage::options())
                            ->required(),
                    ])
                    ->fillForm(fn (Lead $record): array => [
                        'pipeline_stage' => self::stageValue($record),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        $user = Auth::user();

                        app(LeadPipelineService::class)->moveToStage(
                            lead: $record,
                            stage: (string) $data['pipeline_stage'],
                            changedBy: $user instanceof User ? $user : null,
                        );
                    }),

                Action::make('mark_won')
                    ->label('Mark Won')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->visible(fn (Lead $record): bool => self::stageValue($record) !== LeadPipelineStage::WON->value)
                    ->action(function (Lead $record): void {
                        $user = Auth::user();

                        app(LeadPipelineService::class)->moveToStage(
                            lead: $record,
                            stage: LeadPipelineStage::WON,
                            changedBy: $user instanceof User ? $user : null,
                        );
                    }),

                Action::make('mark_lost')
                    ->label('Mark Lost')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Lead $record): bool => self::stageValue($record) !== LeadPipelineStage::LOST->value)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $user = Auth::user();

                        app(LeadPipelineService::class)->moveToStage(
                            lead: $record,
                            stage: LeadPipelineStage::LOST,
                            changedBy: $user instanceof User ? $user : null,
                        );
                    }),

                Action::make('not_interested')
                    ->label('Not Interested')
                    ->icon('heroicon-o-hand-thumb-down')
                    ->color('gray')
                    ->visible(fn (Lead $record): bool => self::stageValue($record) !== LeadPipelineStage::NOT_INTERESTED->value)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $user = Auth::user();

                        app(LeadPipelineService::class)->moveToStage(
                            lead: $record,
                            stage: LeadPipelineStage::NOT_INTERESTED,
                            changedBy: $user instanceof User ? $user : null,
                        );
                    }),

                DeleteAction::make()
                    ->visible(function (Lead $record): bool {
                        $user = Auth::user();

                        return $user instanceof User && $user->isManagerOrGm();
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'));
    }

    private static function stageValue(Lead $lead): string
    {
        $stage = $lead->getAttribute('pipeline_stage');

        return $stage instanceof LeadPipelineStage
            ? $stage->value
            : (string) $stage;
    }

    private static function stageLabel(mixed $state): string
    {
        $stage = $state instanceof LeadPipelineStage
            ? $state
            : LeadPipelineStage::tryFrom((string) $state);

        return $stage?->label() ?? (string) $state;
    }

    private static function stageColor(mixed $state): string
    {
        $stage = $state instanceof LeadPipelineStage
            ? $state
            : LeadPipelineStage::tryFrom((string) $state);

        return match ($stage) {
            LeadPipelineStage::NEW => 'gray',
            LeadPipelineStage::IN_COMMUNICATION => 'info',
            LeadPipelineStage::DID_NOT_ANSWER => 'warning',
            LeadPipelineStage::IN_NEGOTIATION => 'primary',
            LeadPipelineStage::CONTRACT => 'primary',
            LeadPipelineStage::INVOICE => 'warning',
            LeadPipelineStage::WON => 'success',
            LeadPipelineStage::LOST => 'danger',
            LeadPipelineStage::NOT_INTERESTED => 'danger',
            default => 'gray',
        };
    }
}

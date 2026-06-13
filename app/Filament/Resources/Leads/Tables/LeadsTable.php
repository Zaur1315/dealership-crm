<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadPipelineStage;
use App\Models\Lead;
use App\Models\User;
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
                    ->formatStateUsing(
                        fn (LeadPipelineStage|string $state
                        ): string => $state instanceof LeadPipelineStage ? $state->label() : $state
                    )
                    ->color(
                        fn (LeadPipelineStage|string $state
                        ): string => match ($state instanceof LeadPipelineStage ? $state : LeadPipelineStage::tryFrom(
                            $state
                        )) {
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
                        }
                    ),

                TextColumn::make('assignedTo.full_name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable(),

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
                    ->fillForm(function (Lead $record): array {
                        $stage = $record->getAttribute('pipeline_stage');

                        return [
                            'pipeline_stage' => $stage instanceof LeadPipelineStage
                                ? $stage->value
                                : (string) $stage,
                        ];
                    })
                    ->action(function (Lead $record, array $data): void {
                        $record->forceFill([
                            'pipeline_stage' => $data['pipeline_stage'],
                        ])->save();
                    }),
                Action::make('mark_won')
                    ->label('Mark Won')
                    ->icon('heroicon-o-trophy')
                    ->color('success')
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::WON->value)
                    ->action(function (Lead $record): void {
                        $record->forceFill([
                            'pipeline_stage' => LeadPipelineStage::WON->value,
                        ])->save();
                    }),

                Action::make('mark_lost')
                    ->label('Mark Lost')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::LOST->value)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $record->forceFill([
                            'pipeline_stage' => LeadPipelineStage::LOST->value,
                        ])->save();
                    }),

                Action::make('not_interested')
                    ->label('Not Interested')
                    ->icon('heroicon-o-hand-thumb-down')
                    ->color('gray')
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::NOT_INTERESTED->value)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $record->forceFill([
                            'pipeline_stage' => LeadPipelineStage::NOT_INTERESTED->value,
                        ])->save();
                    }),
                DeleteAction::make()
                    ->visible(function (Lead $record): bool {
                        $user = Auth::user();

                        return $user instanceof User && ($user->isGm() || $user->isManager());
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->latest('id'));
    }
}

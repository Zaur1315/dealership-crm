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

                SelectFilter::make('assigned_to_user_id')
                    ->label('Assigned To')
                    ->options(fn (): array => User::query()
                        ->where('status', User::STATUS_ACTIVE)
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->all()),
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
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::WON->value)
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
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::LOST->value)
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
                    ->visible(fn (Lead $record): bool => $record->getAttribute('pipeline_stage') !== LeadPipelineStage::NOT_INTERESTED->value)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $user = Auth::user();

                        app(LeadPipelineService::class)->moveToStage(
                            lead: $record,
                            stage: LeadPipelineStage::NOT_INTERESTED,
                            changedBy: $user instanceof User ? $user : null,
                        );
                    }),

                Action::make('assign_salesperson')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Select::make('assigned_to_user_id')
                            ->label('Assigned To')
                            ->options(fn (): array => User::query()
                                ->where('status', User::STATUS_ACTIVE)
                                ->whereIn('role', [
                                    User::ROLE_GM,
                                    User::ROLE_MANAGER,
                                    User::ROLE_SALESPERSON,
                                ])
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->fillForm(fn (Lead $record): array => [
                        'assigned_to_user_id' => $record->assigned_to_user_id,
                    ])
                    ->action(function (Lead $record, array $data): void {
                        $record->forceFill([
                            'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
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

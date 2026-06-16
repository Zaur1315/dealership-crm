<?php

namespace App\Filament\Resources\Emails\Tables;

use App\Data\Email\OutgoingEmailData;
use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Enums\LeadPipelineStage;
use App\Models\Dealership;
use App\Models\Email;
use App\Models\Lead;
use App\Models\User;
use App\Services\Email\TitanSmtpEmailSender;
use App\Services\Leads\LeadActivityService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('from_email')
                    ->label('From')
                    ->searchable(),

                TextColumn::make('to')
                    ->label('To')
                    ->state(fn (Email $record): string => implode(', ', $record->to ?? []))
                    ->searchable(),

                TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(60)
                    ->placeholder('(No subject)'),

                TextColumn::make('lead.full_name')
                    ->label('Lead')
                    ->placeholder('Unmatched'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),

                IconColumn::make('needs_manual_review')
                    ->label('Review')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('move_to_trash')
                    ->label('Move to Trash')
                    ->icon('heroicon-o-trash')
                    ->color('warning')
                    ->visible(fn (Email $record): bool => $record->getAttribute('status') === EmailStatus::ACTIVE->value)
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::TRASHED->value,
                            'trashed_at' => now(),
                        ])->save();
                    }),

                Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->visible(fn (Email $record): bool => $record->getAttribute('status') === EmailStatus::TRASHED->value)
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::HIDDEN->value,
                            'hidden_at' => now(),
                        ])->save();
                    }),

                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Email $record): bool => in_array($record->getAttribute('status'), [
                        EmailStatus::TRASHED->value,
                        EmailStatus::HIDDEN->value,
                    ], true))
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::ACTIVE->value,
                            'trashed_at' => null,
                            'hidden_at' => null,
                        ])->save();
                    }),

                Action::make('delete_permanently')
                    ->label('Delete Permanently')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(function (Email $record): bool {
                        $user = Auth::user();

                        return $user instanceof User
                            && $user->isGm()
                            && $record->getAttribute('status') === EmailStatus::HIDDEN->value;
                    })
                    ->requiresConfirmation()
                    ->action(function (Email $record): void {
                        $record->forceFill([
                            'status' => EmailStatus::DELETED->value,
                            'deleted_at' => now(),
                        ])->save();
                    }),

                Action::make('link_to_lead')
                    ->label('Link to Lead')
                    ->icon('heroicon-o-link')
                    ->visible(fn (Email $record): bool => $record->lead_id === null)
                    ->schema([
                        Select::make('lead_id')
                            ->label('Lead')
                            ->options(fn (Email $record): array => Lead::query()
                                ->where('dealership_id', $record->dealership_id)
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (Email $record, array $data): void {
                        $lead = Lead::query()->find($data['lead_id']);

                        if (! $lead instanceof Lead) {
                            Notification::make()
                                ->title('Lead not found.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->forceFill([
                            'lead_id' => $lead->id,
                            'is_matched_to_lead' => true,
                            'needs_manual_review' => false,
                        ])->save();

                        Notification::make()
                            ->title('Email linked to lead.')
                            ->success()
                            ->send();
                    }),

                Action::make('create_lead')
                    ->label('Create Lead')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (Email $record): bool => $record->lead_id === null)
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->default(fn (Email $record): string => $record->from_name ?: $record->from_email)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->default('Unknown')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->default(fn (Email $record): string => $record->from_email)
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Email $record, array $data): void {
                        $user = Auth::user();

                        $lead = Lead::query()->create([
                            'dealership_id' => $record->dealership_id,
                            'created_by_user_id' => $user instanceof User ? $user->id : null,
                            'created_by_name' => $user instanceof User ? $user->full_name : null,
                            'full_name' => $data['full_name'],
                            'phone_number' => $data['phone_number'],
                            'email' => $data['email'],
                            'pipeline_stage' => LeadPipelineStage::NEW->value,
                        ]);

                        $record->forceFill([
                            'lead_id' => $lead->id,
                            'is_matched_to_lead' => true,
                            'needs_manual_review' => false,
                        ])->save();

                        app(LeadActivityService::class)->leadCreated(
                            lead: $lead,
                            user: $user instanceof User ? $user : null,
                        );

                        Notification::make()
                            ->title('Lead created and email linked.')
                            ->success()
                            ->send();
                    }),

                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (Email $record): bool => $record->getAttribute('direction') === EmailDirection::INBOUND->value)
                    ->schema([
                        TextInput::make('to')
                            ->label('To')
                            ->email()
                            ->default(fn (Email $record): string => $record->from_email)
                            ->required(),

                        TextInput::make('subject')
                            ->label('Subject')
                            ->default(fn (Email $record): string => str_starts_with((string) $record->subject, 'Re:')
                                ? (string) $record->subject
                                : 'Re: '.(string) ($record->subject ?? ''))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body_text')
                            ->label('Message')
                            ->required()
                            ->rows(8),
                    ])
                    ->action(function (Email $record, array $data): void {
                        $dealership = $record->dealership;
                        $user = Auth::user();

                        if (! $dealership instanceof Dealership) {
                            Notification::make()
                                ->title('Dealership not found.')
                                ->danger()
                                ->send();

                            return;
                        }

                        app(TitanSmtpEmailSender::class)->send(
                            dealership: $dealership,
                            data: new OutgoingEmailData(
                                to: [(string) $data['to']],
                                subject: (string) $data['subject'],
                                bodyText: (string) $data['body_text'],
                            ),
                            lead: $record->lead instanceof Lead ? $record->lead : null,
                            user: $user instanceof User ? $user : null,
                        );

                        Notification::make()
                            ->title('Reply sent.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

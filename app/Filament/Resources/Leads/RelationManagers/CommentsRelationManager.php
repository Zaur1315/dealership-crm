<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Models\LeadComment;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('body')
                    ->label('Comment')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('author_name')
                    ->label('Author')
                    ->formatStateUsing(function (?string $state, LeadComment $record): string {
                        if ($record->author instanceof User) {
                            return $record->author->full_name;
                        }

                        if ($state !== null && $state !== '') {
                            return $state.' - Deleted User';
                        }

                        return 'Deleted User';
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Comment')
                    ->schema([
                        Textarea::make('body')
                            ->label('Comment')
                            ->required()
                            ->rows(4)
                            ->maxLength(5000),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        $user = Auth::user();

                        if ($user instanceof User) {
                            $data['user_id'] = $user->id;
                            $data['author_name'] = $user->full_name;
                        }

                        return $data;
                    })
                    ->successNotification(
                        Notification::make()
                            ->title('Comment added.')
                            ->success()
                    ),
            ])
            ->recordActions([])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}

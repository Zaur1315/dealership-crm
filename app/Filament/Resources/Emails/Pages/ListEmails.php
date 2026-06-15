<?php

namespace App\Filament\Resources\Emails\Pages;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListEmails extends ListRecords
{
    protected static string $resource = EmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'inbox' => Tab::make('Inbox')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('direction', EmailDirection::INBOUND->value)
                    ->where('status', EmailStatus::ACTIVE->value)),

            'sent' => Tab::make('Sent')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('direction', EmailDirection::OUTBOUND->value)
                    ->where('status', EmailStatus::ACTIVE->value)),

            'trash' => Tab::make('Trash')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', EmailStatus::TRASHED->value)),

            'hidden' => Tab::make('Hidden')
                ->visible(fn (): bool => Auth::user() instanceof User && ! Auth::user()->isSalesperson())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', EmailStatus::HIDDEN->value)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'inbox';
    }
}

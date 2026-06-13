<?php

namespace App\Filament\Resources\CrmNotifications\Pages;

use App\Filament\Resources\CrmNotifications\CrmNotificationResource;
use App\Models\CrmNotification;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListCrmNotifications extends ListRecords
{
    protected static string $resource = CrmNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Mark all as read')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => CrmNotification::query()
                    ->where('recipient_user_id', Auth::id())
                    ->whereNull('read_at')
                    ->exists())
                ->action(function (): void {
                    CrmNotification::query()
                        ->where('recipient_user_id', Auth::id())
                        ->whereNull('read_at')
                        ->update([
                            'read_at' => now(),
                            'updated_at' => now(),
                        ]);
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'unread' => Tab::make('Unread')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('read_at')),

            'all' => Tab::make('All'),

            'read' => Tab::make('Read')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNotNull('read_at')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'unread';
    }
}

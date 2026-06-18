<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Filament\Resources\CrmNotifications\CrmNotificationResource;
use App\Models\CrmNotification;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllAsRead(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        CrmNotification::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = $this->findNotification($notificationId);

        if (! $notification instanceof CrmNotification) {
            return;
        }

        $notification->forceFill([
            'read_at' => now(),
        ])->save();
    }

    public function openNotification(int $notificationId): mixed
    {
        $notification = $this->findNotification($notificationId);

        if (! $notification instanceof CrmNotification) {
            return null;
        }

        $notification->forceFill([
            'read_at' => now(),
        ])->save();

        return redirect()->to(
            CrmNotificationResource::getUrl('view', [
                'record' => $notification,
            ]),
        );
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'livewire.notification-bell';

        return view($view, [
            'unreadCount' => $this->unreadCount(),
            'notifications' => $this->notifications(),
        ]);
    }

    private function unreadCount(): int
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return 0;
        }

        return CrmNotification::query()
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * @return Collection<int, CrmNotification>
     */
    private function notifications(): Collection
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return new Collection;
        }

        return CrmNotification::query()
            ->where('recipient_user_id', $user->id)
            ->latest()
            ->limit(8)
            ->get();
    }

    private function findNotification(int $notificationId): ?CrmNotification
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        return CrmNotification::query()
            ->whereKey($notificationId)
            ->where('recipient_user_id', $user->id)
            ->first();
    }
}

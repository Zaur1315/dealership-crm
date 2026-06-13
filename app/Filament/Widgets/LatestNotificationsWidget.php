<?php

namespace App\Filament\Widgets;

use App\Models\CrmNotification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class LatestNotificationsWidget extends Widget
{
    protected string $view = 'filament.widgets.latest-notifications-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Collection<int, CrmNotification>
     */
    public function getNotifications(): Collection
    {
        return CrmNotification::query()
            ->where('recipient_user_id', Auth::id())
            ->whereNull('read_at')
            ->latest('id')
            ->limit(5)
            ->get();
    }
}

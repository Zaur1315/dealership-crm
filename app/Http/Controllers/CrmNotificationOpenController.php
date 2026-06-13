<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CrmNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CrmNotificationOpenController extends Controller
{
    public function __invoke(CrmNotification $notification): RedirectResponse
    {
        abort_unless($notification->recipient_user_id === Auth::id(), 404);

        $notification->markAsRead();

        if ($notification->target_url === null) {
            return redirect()->route('filament.admin.resources.crm-notifications.index');
        }

        return redirect($notification->target_url);
    }
}

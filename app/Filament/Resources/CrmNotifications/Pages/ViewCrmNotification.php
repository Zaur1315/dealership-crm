<?php

namespace App\Filament\Resources\CrmNotifications\Pages;

use App\Filament\Resources\CrmNotifications\CrmNotificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrmNotification extends ViewRecord
{
    protected static string $resource = CrmNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CrmNotifications\Pages;

use App\Filament\Resources\CrmNotifications\CrmNotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCrmNotification extends EditRecord
{
    protected static string $resource = CrmNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

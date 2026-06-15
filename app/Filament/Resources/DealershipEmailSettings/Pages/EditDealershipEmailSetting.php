<?php

namespace App\Filament\Resources\DealershipEmailSettings\Pages;

use App\Filament\Resources\DealershipEmailSettings\DealershipEmailSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDealershipEmailSetting extends EditRecord
{
    protected static string $resource = DealershipEmailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

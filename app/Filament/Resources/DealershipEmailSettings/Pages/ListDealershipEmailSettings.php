<?php

namespace App\Filament\Resources\DealershipEmailSettings\Pages;

use App\Filament\Resources\DealershipEmailSettings\DealershipEmailSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDealershipEmailSettings extends ListRecords
{
    protected static string $resource = DealershipEmailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

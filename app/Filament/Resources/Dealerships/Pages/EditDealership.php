<?php

namespace App\Filament\Resources\Dealerships\Pages;

use App\Filament\Resources\Dealerships\DealershipResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDealership extends EditRecord
{
    protected static string $resource = DealershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

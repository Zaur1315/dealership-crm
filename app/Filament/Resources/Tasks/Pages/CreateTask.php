<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        $user = Auth::user();

        $data['dealership_id'] = $dealership->id;

        if ($user instanceof User) {
            $data['created_by_user_id'] = $user->id;
            $data['created_by_name'] = $user->full_name;
        }

        return $data;
    }
}

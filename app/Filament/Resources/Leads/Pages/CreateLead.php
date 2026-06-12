<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\User;
use App\Services\Tasks\LeadTaskAutomationService;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;

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

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if ($record instanceof Lead) {
            app(LeadTaskAutomationService::class)->createContactLeadTask(
                lead: $record,
                createdBy: Auth::user() instanceof User ? Auth::user() : null,
            );
        }

        return $record;
    }
}

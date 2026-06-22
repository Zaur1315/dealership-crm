<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadActivityService;
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
        $user = Auth::user();
        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        $data['dealership_id'] = $dealership->id;

        if ($user instanceof User) {
            $data['created_by_user_id'] = $user->id;
            $data['created_by_name'] = $user->full_name;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Lead $lead */
        $lead = parent::handleRecordCreation($data);

        $currentUser = Auth::user();

        app(LeadActivityService::class)->leadCreated(
            lead: $lead,
            user: $currentUser instanceof User ? $currentUser : null,
        );

        $task = app(LeadTaskAutomationService::class)->createContactLeadTask(
            lead: $lead,
            createdBy: $currentUser instanceof User ? $currentUser : null,
        );

        app(LeadActivityService::class)->taskCreated(
            task: $task,
            user: $currentUser instanceof User ? $currentUser : null,
        );

        return $lead;
    }
}

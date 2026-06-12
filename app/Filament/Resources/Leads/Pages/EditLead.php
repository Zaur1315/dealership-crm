<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadPipelineStage;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;

        if (! $record instanceof Lead) {
            return $data;
        }

        $stage = LeadPipelineStage::tryFrom((string) ($data['pipeline_stage'] ?? ''));

        if ($stage === LeadPipelineStage::IN_COMMUNICATION && blank($record->first_communication_at)) {
            $data['first_communication_at'] = now();
        }

        if ($stage === LeadPipelineStage::WON && blank($record->won_at)) {
            $data['won_at'] = now();
        }

        if ($stage === LeadPipelineStage::LOST && blank($record->lost_at)) {
            $data['lost_at'] = now();
        }

        if ($stage === LeadPipelineStage::NOT_INTERESTED && blank($record->not_interested_at)) {
            $data['not_interested_at'] = now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

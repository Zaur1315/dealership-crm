<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Enums\LeadPipelineStage;
use App\Models\Lead;
use App\Models\User;
use App\Services\Tasks\LeadTaskAutomationService;

class LeadPipelineService
{
    public function moveToStage(Lead $lead, LeadPipelineStage|string $stage, ?User $changedBy = null): Lead
    {
        $stage = $stage instanceof LeadPipelineStage
            ? $stage
            : LeadPipelineStage::from($stage);

        $oldStage = $lead->getAttribute('pipeline_stage');

        $oldStageValue = $oldStage instanceof LeadPipelineStage
            ? $oldStage->value
            : (string) $oldStage;

        $lead->forceFill([
            'pipeline_stage' => $stage->value,
            ...$this->stageTimestampData($lead, $stage),
        ])->save();

        if ($oldStageValue !== $stage->value) {
            app(LeadActivityService::class)->stageChanged(
                lead: $lead,
                oldStage: $oldStageValue,
                newStage: $stage->value,
                user: $changedBy,
            );
        }

        if ($stage === LeadPipelineStage::DID_NOT_ANSWER) {
            app(LeadTaskAutomationService::class)->createDidNotAnswerSequence(
                lead: $lead,
                createdBy: $changedBy,
            );
        }

        return $lead;
    }

    /**
     * @return array<string, mixed>
     */
    private function stageTimestampData(Lead $lead, LeadPipelineStage $stage): array
    {
        return match ($stage) {
            LeadPipelineStage::IN_COMMUNICATION => blank($lead->first_communication_at)
                ? ['first_communication_at' => now()]
                : [],

            LeadPipelineStage::WON => blank($lead->won_at)
                ? ['won_at' => now()]
                : [],

            LeadPipelineStage::LOST => blank($lead->lost_at)
                ? ['lost_at' => now()]
                : [],

            LeadPipelineStage::NOT_INTERESTED => blank($lead->not_interested_at)
                ? ['not_interested_at' => now()]
                : [],

            default => [],
        };
    }
}

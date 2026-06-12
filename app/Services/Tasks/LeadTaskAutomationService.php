<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\LeadPipelineStage;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;

class LeadTaskAutomationService
{
    public function createContactLeadTask(Lead $lead, ?User $createdBy = null): Task
    {
        return Task::query()->firstOrCreate(
            [
                'lead_id' => $lead->id,
                'type' => TaskType::GENERAL->value,
                'title' => 'Contact this lead',
            ],
            [
                'dealership_id' => $lead->dealership_id,
                'created_by_user_id' => $createdBy?->id,
                'created_by_name' => $createdBy?->full_name,
                'description' => 'Initial follow-up task generated automatically when the lead was created.',
                'status' => TaskStatus::ACTIVE->value,
                'due_at' => now()->addHour(),
            ],
        );
    }

    public function createDidNotAnswerSequence(Lead $lead, ?User $createdBy = null): void
    {
        $pipelineStage = $lead->getAttribute('pipeline_stage');

        $stage = $pipelineStage instanceof LeadPipelineStage
            ? $pipelineStage
            : LeadPipelineStage::tryFrom((string) $pipelineStage);

        if ($stage !== LeadPipelineStage::DID_NOT_ANSWER) {
            return;
        }

        $existingSequenceCount = Task::query()
            ->where('lead_id', $lead->id)
            ->where('description', 'like', 'Did Not Answer follow-up sequence%')
            ->count();

        if ($existingSequenceCount > 0) {
            return;
        }

        $sequence = [
            [
                'title' => 'Follow up by phone',
                'type' => TaskType::PHONE_CALL,
                'due_at' => now()->addHours(2),
            ],
            [
                'title' => 'Send follow-up email',
                'type' => TaskType::EMAIL,
                'due_at' => now()->addHours(6),
            ],
            [
                'title' => 'Second phone call follow-up',
                'type' => TaskType::PHONE_CALL,
                'due_at' => now()->addDay(),
            ],
            [
                'title' => 'Second follow-up email',
                'type' => TaskType::EMAIL,
                'due_at' => now()->addDay()->addHours(4),
            ],
            [
                'title' => 'Final phone call follow-up',
                'type' => TaskType::PHONE_CALL,
                'due_at' => now()->addDays(3),
            ],
            [
                'title' => 'Final follow-up email',
                'type' => TaskType::EMAIL,
                'due_at' => now()->addDays(3)->addHours(4),
            ],
        ];

        foreach ($sequence as $index => $item) {
            Task::query()->create([
                'dealership_id' => $lead->dealership_id,
                'lead_id' => $lead->id,
                'created_by_user_id' => $createdBy?->id,
                'created_by_name' => $createdBy?->full_name,
                'title' => $item['title'],
                'description' => 'Did Not Answer follow-up sequence task #'.($index + 1),
                'type' => $item['type']->value,
                'status' => TaskStatus::ACTIVE->value,
                'due_at' => $item['due_at'],
            ]);
        }
    }
}

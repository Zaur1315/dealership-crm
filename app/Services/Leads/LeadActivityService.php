<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Enums\LeadActivityType;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadComment;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;

class LeadActivityService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        Lead $lead,
        LeadActivityType $type,
        string $title,
        ?string $description = null,
        ?User $user = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): LeadActivity {
        return LeadActivity::query()->create([
            'dealership_id' => $lead->dealership_id,
            'lead_id' => $lead->id,
            'user_id' => $user?->id,
            'user_name' => $user?->full_name,
            'type' => $type->value,
            'title' => $title,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function leadCreated(Lead $lead, ?User $user = null): LeadActivity
    {
        return $this->record(
            lead: $lead,
            type: LeadActivityType::LEAD_CREATED,
            title: 'Lead created',
            description: $lead->full_name.' was added to the CRM.',
            user: $user,
            subjectType: Lead::class,
            subjectId: $lead->id,
        );
    }

    public function commentCreated(LeadComment $comment, ?User $user = null): ?LeadActivity
    {
        $lead = $comment->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return $this->record(
            lead: $lead,
            type: LeadActivityType::COMMENT_CREATED,
            title: 'Comment added',
            description: $comment->body,
            user: $user,
            subjectType: LeadComment::class,
            subjectId: $comment->id,
        );
    }

    public function stageChanged(
        Lead $lead,
        string $oldStage,
        string $newStage,
        ?User $user = null,
    ): LeadActivity {
        return $this->record(
            lead: $lead,
            type: LeadActivityType::STAGE_CHANGED,
            title: 'Stage changed',
            description: 'Lead stage was changed.',
            user: $user,
            subjectType: Lead::class,
            subjectId: $lead->id,
            oldValues: [
                'pipeline_stage' => $oldStage,
            ],
            newValues: [
                'pipeline_stage' => $newStage,
            ],
        );
    }

    public function assignedUserChanged(
        Lead $lead,
        ?int $oldUserId,
        ?int $newUserId,
        ?User $user = null,
    ): LeadActivity {
        return $this->record(
            lead: $lead,
            type: LeadActivityType::ASSIGNED_USER_CHANGED,
            title: 'Assigned user changed',
            description: 'Lead assignment was changed.',
            user: $user,
            subjectType: Lead::class,
            subjectId: $lead->id,
            oldValues: [
                'assigned_to_user_id' => $oldUserId,
            ],
            newValues: [
                'assigned_to_user_id' => $newUserId,
            ],
        );
    }

    public function taskCreated(Task $task, ?User $user = null): ?LeadActivity
    {
        $lead = $task->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return $this->record(
            lead: $lead,
            type: LeadActivityType::TASK_CREATED,
            title: 'Task created',
            description: $task->title,
            user: $user,
            subjectType: Task::class,
            subjectId: $task->id,
            newValues: [
                'title' => $task->title,
                'type' => $this->attributeToString($task->getAttribute('type')),
                'status' => $this->attributeToString($task->getAttribute('status')),
                'due_at' => $this->dateTimeToString($task->getAttribute('due_at')),
            ],
        );
    }

    public function taskCompleted(Task $task, ?User $user = null): ?LeadActivity
    {
        $lead = $task->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return $this->record(
            lead: $lead,
            type: LeadActivityType::TASK_COMPLETED,
            title: 'Task completed',
            description: $task->title,
            user: $user,
            subjectType: Task::class,
            subjectId: $task->id,
            oldValues: [
                'status' => 'active',
            ],
            newValues: [
                'status' => 'completed',
                'completed_at' => $this->dateTimeToString($task->getAttribute('completed_at')),
            ],
        );
    }

    public function taskExpired(Task $task): ?LeadActivity
    {
        $lead = $task->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return $this->record(
            lead: $lead,
            type: LeadActivityType::TASK_EXPIRED,
            title: 'Task expired',
            description: $task->title,
            user: null,
            subjectType: Task::class,
            subjectId: $task->id,
            oldValues: [
                'status' => 'active',
            ],
            newValues: [
                'status' => 'expired',
                'expired_at' => $this->dateTimeToString($task->getAttribute('expired_at')),
            ],
        );
    }

    private function dateTimeToString(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }

    private function attributeToString(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function taskUpdated(Task $task, array $oldValues, array $newValues, ?User $user = null): ?LeadActivity
    {
        $lead = $task->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return $this->record(
            lead: $lead,
            type: LeadActivityType::TASK_UPDATED,
            title: 'Task updated',
            description: $task->title,
            user: $user,
            subjectType: Task::class,
            subjectId: $task->id,
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }
}

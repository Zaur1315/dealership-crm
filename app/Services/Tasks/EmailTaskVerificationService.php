<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use App\Models\Email;
use App\Models\Lead;
use App\Models\Task;

class EmailTaskVerificationService
{
    public function hasOutgoingEmailForTask(Task $task): bool
    {
        return $this->findOutgoingEmailForTask($task) instanceof Email;
    }

    public function findOutgoingEmailForTask(Task $task): ?Email
    {
        $lead = $task->lead;

        if (! $lead instanceof Lead) {
            return null;
        }

        return Email::query()
            ->where('dealership_id', $task->dealership_id)
            ->where('lead_id', $lead->id)
            ->where('direction', EmailDirection::OUTBOUND->value)
            ->where('status', EmailStatus::ACTIVE->value)
            ->whereJsonContains('to', $lead->email)
            ->latest('id')
            ->first();
    }
}

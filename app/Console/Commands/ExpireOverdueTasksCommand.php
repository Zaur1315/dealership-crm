<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\Notifications\CrmNotificationService;
use Illuminate\Console\Command;

class ExpireOverdueTasksCommand extends Command
{
    protected $signature = 'app:tasks:expire-overdue';

    protected $description = 'Mark active overdue tasks as expired.';

    public function handle(CrmNotificationService $notificationService): int
    {
        $tasks = Task::query()
            ->where('status', TaskStatus::ACTIVE->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get();

        foreach ($tasks as $task) {

            $task->forceFill([
                'status' => TaskStatus::EXPIRED->value,
                'expired_at' => now(),
            ])->save();

            $notificationService->notifyTaskExpired($task);
        }

        $this->info("Expired {$tasks->count()} overdue task(s).");

        return self::SUCCESS;
    }
}

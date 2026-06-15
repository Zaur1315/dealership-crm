<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\Notifications\CrmNotificationService;
use Illuminate\Console\Command;

class NotifyDueTasksCommand extends Command
{
    protected $signature = 'app:tasks:notify-due';

    protected $description = 'Notify users about active tasks that passed their due date.';

    public function handle(CrmNotificationService $notificationService): int
    {
        $tasks = Task::query()
            ->where('status', TaskStatus::ACTIVE->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->get();

        foreach ($tasks as $task) {
            $notificationService->notifyTaskDue($task);
        }

        $this->info("Checked {$tasks->count()} due task(s).");

        return self::SUCCESS;
    }
}

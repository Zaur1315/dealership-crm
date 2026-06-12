<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Console\Command;

class ExpireOverdueTasksCommand extends Command
{
    protected $signature = 'app:tasks:expire-overdue';

    protected $description = 'Mark active overdue tasks as expired.';

    public function handle(): int
    {
        $count = Task::query()
            ->where('status', TaskStatus::ACTIVE->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update([
                'status' => TaskStatus::EXPIRED->value,
                'expired_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Expired {$count} overdue task(s).");

        return self::SUCCESS;
    }
}

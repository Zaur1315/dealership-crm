<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CrmNotification;
use Illuminate\Console\Command;

class PruneExpiredCrmNotificationsCommand extends Command
{
    protected $signature = 'app:crm-notifications:prune-expired';

    protected $description = 'Delete expired CRM notifications.';

    public function handle(): int
    {
        $count = CrmNotification::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$count} expired CRM notification(s).");

        return self::SUCCESS;
    }
}

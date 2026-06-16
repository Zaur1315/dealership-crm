<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DealershipEmailSetting;
use App\Services\Email\TitanImapInboxSyncService;
use Illuminate\Console\Command;

class SyncTitanInboxCommand extends Command
{
    protected $signature = 'app:emails:sync-inbox {--setting-id= : Sync only one dealership email setting}';

    protected $description = 'Sync inbound emails from Titan mailboxes.';

    public function handle(TitanImapInboxSyncService $syncService): int
    {
        $query = DealershipEmailSetting::query()
            ->where('is_active', true);

        $settingId = $this->option('setting-id');

        if (is_string($settingId) && $settingId !== '') {
            $query->whereKey((int) $settingId);
        }

        $settings = $query->get();

        $total = 0;

        foreach ($settings as $setting) {
            $synced = $syncService->sync($setting);

            $this->info("Synced {$synced} email(s) for setting #{$setting->id}.");

            $total += $synced;
        }

        $this->info("Total synced: {$total} email(s).");

        return self::SUCCESS;
    }
}

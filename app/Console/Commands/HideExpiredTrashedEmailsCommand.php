<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EmailStatus;
use App\Models\Email;
use Illuminate\Console\Command;

class HideExpiredTrashedEmailsCommand extends Command
{
    protected $signature = 'app:emails:hide-expired-trash';

    protected $description = 'Move trashed emails older than 30 days to hidden status.';

    public function handle(): int
    {
        $emails = Email::query()
            ->where('status', EmailStatus::TRASHED->value)
            ->whereNotNull('trashed_at')
            ->where('trashed_at', '<=', now()->subDays(30))
            ->get();

        foreach ($emails as $email) {
            $email->forceFill([
                'status' => EmailStatus::HIDDEN->value,
                'hidden_at' => now(),
            ])->save();
        }

        $this->info("Hidden {$emails->count()} expired trashed email(s).");

        return self::SUCCESS;
    }
}

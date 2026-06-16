<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailSetupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealershipEmailSetting extends Model
{
    protected $fillable = [
        'dealership_id',
        'domain',
        'domain_provider',
        'mailbox_provider',
        'from_email',
        'from_name',
        'titan_account_reference',
        'dns_status',
        'mailbox_status',
        'sending_status',
        'is_active',
        'email_address',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
    ];

    protected function casts(): array
    {
        return [
            'dns_status' => EmailSetupStatus::class,
            'mailbox_status' => EmailSetupStatus::class,
            'sending_status' => EmailSetupStatus::class,
            'is_active' => 'boolean',
            'imap_password' => 'encrypted',
            'smtp_password' => 'encrypted',
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
        ];
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}

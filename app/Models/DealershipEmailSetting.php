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
        'sending_provider',
        'from_email',
        'from_name',
        'resend_api_key',
        'resend_webhook_secret',
        'titan_email',
        'titan_account_reference',
        'titan_api_key',
        'dns_status',
        'mailbox_status',
        'sending_status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'resend_api_key' => 'encrypted',
            'resend_webhook_secret' => 'encrypted',
            'titan_api_key' => 'encrypted',
            'dns_status' => EmailSetupStatus::class,
            'mailbox_status' => EmailSetupStatus::class,
            'sending_status' => EmailSetupStatus::class,
            'is_active' => 'boolean',
        ];
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}

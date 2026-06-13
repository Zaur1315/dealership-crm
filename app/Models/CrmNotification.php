<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CrmNotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmNotification extends Model
{
    protected $fillable = [
        'dealership_id',
        'recipient_user_id',
        'type',
        'title',
        'body',
        'target_type',
        'target_id',
        'target_url',
        'payload',
        'read_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CrmNotificationType::class,
            'payload' => 'array',
            'read_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->forceFill([
            'read_at' => now(),
        ])->save();
    }
}

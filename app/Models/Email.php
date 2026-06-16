<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailDirection;
use App\Enums\EmailStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    protected $fillable = [
        'dealership_id',
        'lead_id',
        'created_by_user_id',
        'provider',
        'provider_message_id',
        'direction',
        'status',
        'from_email',
        'from_name',
        'to',
        'cc',
        'bcc',
        'subject',
        'body_text',
        'body_html',
        'sent_at',
        'received_at',
        'trashed_at',
        'hidden_at',
        'deleted_at',
        'is_matched_to_lead',
        'needs_manual_review',
    ];

    protected function casts(): array
    {
        return [
            'direction' => EmailDirection::class,
            'status' => EmailStatus::class,
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'trashed_at' => 'datetime',
            'hidden_at' => 'datetime',
            'deleted_at' => 'datetime',
            'is_matched_to_lead' => 'boolean',
            'needs_manual_review' => 'boolean',
        ];
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }
}

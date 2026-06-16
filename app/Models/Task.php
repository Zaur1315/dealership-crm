<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'dealership_id',
        'lead_id',
        'created_by_user_id',
        'created_by_name',
        'completed_by_user_id',
        'title',
        'description',
        'type',
        'status',
        'due_at',
        'completed_at',
        'expired_at',
        'verified_email_id',
        'completed_with_email_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TaskType::class,
            'status' => TaskStatus::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'expired_at' => 'datetime',
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

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function statusEnum(): ?TaskStatus
    {
        $status = $this->getAttribute('status');

        if ($status instanceof TaskStatus) {
            return $status;
        }

        return TaskStatus::tryFrom((string) $status);
    }

    public function typeEnum(): ?TaskType
    {
        $type = $this->getAttribute('type');

        if ($type instanceof TaskType) {
            return $type;
        }

        return TaskType::tryFrom((string) $type);
    }

    public function isActive(): bool
    {
        return $this->statusEnum() === TaskStatus::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->statusEnum() === TaskStatus::COMPLETED;
    }

    public function isExpired(): bool
    {
        return $this->statusEnum() === TaskStatus::EXPIRED;
    }

    public function isEmailTask(): bool
    {
        return $this->typeEnum() === TaskType::EMAIL;
    }

    public function completedWithEmail(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'completed_with_email_id');
    }
}

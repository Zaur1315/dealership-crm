<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadPipelineStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'dealership_id',
        'created_by_user_id',
        'created_by_name',
        'full_name',
        'phone_number',
        'email',
        'address',
        'deal_value',
        'pipeline_stage',
        'first_communication_at',
        'won_at',
        'lost_at',
        'not_interested_at',
        'assigned_to_user_id',
    ];

    protected function casts(): array
    {
        return [
            'deal_value' => 'decimal:2',
            'pipeline_stage' => LeadPipelineStage::class,
            'first_communication_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
            'not_interested_at' => 'datetime',
        ];
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LeadComment::class)
            ->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'dealership_id',
        'lead_id',
        'user_id',
        'user_name',
        'type',
        'title',
        'description',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeadActivityType::class,
            'old_values' => 'array',
            'new_values' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

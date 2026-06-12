<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadComment extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'author_name',
        'body',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function displayAuthorName(): string
    {
        if ($this->author instanceof User) {
            return $this->author->full_name;
        }

        if ($this->author_name !== null && $this->author_name !== '') {
            return $this->author_name.' - Deleted User';
        }

        return 'Deleted User';
    }
}

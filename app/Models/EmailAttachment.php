<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAttachment extends Model
{
    protected $fillable = [
        'email_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}

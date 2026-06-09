<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Filament\Models\Contracts\HasName;

/**
 * @property int $id
 * @property string $full_name
 * @property string $username
 * @property string $role
 * @property string $status
 * @property string|null $telegram_contact
 * @property int $failed_login_attempts
 * @property Carbon|null $locked_at
 * @property Carbon|null $last_login_at
 * @property Collection<int, Dealership> $dealerships
 * @property Collection<int, LoginAudit> $loginAudits
 */
class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    public const ROLE_GM = 'gm';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_SALESPERSON = 'salesperson';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_LOCKED = 'locked';

    public const STATUS_DEACTIVATED = 'deactivated';

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive();
    }

    public function getFilamentName(): string
    {
        return $this->full_name ?: $this->username;
    }

    protected $fillable = [
        'full_name',
        'username',
        'password',
        'role',
        'status',
        'telegram_contact',
        'failed_login_attempts',
        'locked_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'locked_at' => 'datetime',
            'last_login_at' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    public function dealerships(): BelongsToMany
    {
        return $this->belongsToMany(Dealership::class)
            ->withTimestamps();
    }

    public function loginAudits(): HasMany
    {
        return $this->hasMany(LoginAudit::class);
    }

    public function isGm(): bool
    {
        return $this->role === self::ROLE_GM;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isSalesperson(): bool
    {
        return $this->role === self::ROLE_SALESPERSON;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isDeactivated(): bool
    {
        return $this->status === self::STATUS_DEACTIVATED;
    }
}

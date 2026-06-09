<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case LOCKED = 'locked';
    case DEACTIVATED = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::LOCKED => 'Locked',
            self::DEACTIVATED => 'Deactivated',
        };
    }
}

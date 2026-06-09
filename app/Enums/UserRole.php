<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case GM = 'gm';
    case MANAGER = 'manager';
    case SALESPERSON = 'salesperson';

    public function label(): string
    {
        return match ($this) {
            self::GM => 'Owner / GM',
            self::MANAGER => 'Manager',
            self::SALESPERSON => 'Salesperson',
        };
    }
}

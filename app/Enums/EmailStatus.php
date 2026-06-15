<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailStatus: string
{
    case ACTIVE = 'active';
    case TRASHED = 'trashed';
    case HIDDEN = 'hidden';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::TRASHED => 'Trashed',
            self::HIDDEN => 'Hidden',
            self::DELETED => 'Deleted',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}

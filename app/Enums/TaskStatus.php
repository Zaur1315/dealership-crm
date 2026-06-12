<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::EXPIRED => 'Expired',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}

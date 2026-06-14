<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditLogAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::UPDATED => 'Updated',
            self::DELETED => 'Deleted',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $action): array => [$action->value => $action->label()])
            ->all();
    }
}

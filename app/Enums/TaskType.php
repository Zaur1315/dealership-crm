<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskType: string
{
    case PHONE_CALL = 'phone_call';
    case EMAIL = 'email';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::PHONE_CALL => 'Phone Call',
            self::EMAIL => 'Email',
            self::GENERAL => 'General',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}

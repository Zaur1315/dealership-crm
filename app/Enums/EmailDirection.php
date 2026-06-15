<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailDirection: string
{
    case INBOUND = 'inbound';
    case OUTBOUND = 'outbound';

    public function label(): string
    {
        return match ($this) {
            self::INBOUND => 'Inbound',
            self::OUTBOUND => 'Outbound',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $direction): array => [$direction->value => $direction->label()])
            ->all();
    }
}

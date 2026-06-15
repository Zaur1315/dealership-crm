<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailSetupStatus: string
{
    case NOT_CONFIGURED = 'not_configured';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::NOT_CONFIGURED => 'Not Configured',
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::FAILED => 'Failed',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadActivityType: string
{
    case LEAD_CREATED = 'lead_created';
    case COMMENT_CREATED = 'comment_created';
    case TASK_CREATED = 'task_created';
    case TASK_COMPLETED = 'task_completed';
    case TASK_EXPIRED = 'task_expired';
    case STAGE_CHANGED = 'stage_changed';
    case ASSIGNED_USER_CHANGED = 'assigned_user_changed';
    case TASK_UPDATED = 'task_updated';

    public function label(): string
    {
        return match ($this) {
            self::LEAD_CREATED => 'Lead Created',
            self::COMMENT_CREATED => 'Comment Added',
            self::TASK_CREATED => 'Task Created',
            self::TASK_COMPLETED => 'Task Completed',
            self::TASK_EXPIRED => 'Task Expired',
            self::STAGE_CHANGED => 'Stage Changed',
            self::ASSIGNED_USER_CHANGED => 'Assigned User Changed',
            self::TASK_UPDATED => 'Task Updated',
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

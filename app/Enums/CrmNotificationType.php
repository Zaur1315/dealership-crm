<?php

declare(strict_types=1);

namespace App\Enums;

enum CrmNotificationType: string
{
    case NEW_LEAD = 'new_lead';
    case TASK_DUE = 'task_due';
    case TASK_EXPIRED = 'task_expired';
    case OVERDUE_TASKS_ON_LOGIN = 'overdue_tasks_on_login';
    case INVOICE_ALERT = 'invoice_alert';
    case NEW_EMAIL = 'new_email';

    public function label(): string
    {
        return match ($this) {
            self::NEW_LEAD => 'New Lead',
            self::TASK_DUE => 'Task Due',
            self::TASK_EXPIRED => 'Task Expired',
            self::OVERDUE_TASKS_ON_LOGIN => 'Overdue Tasks',
            self::INVOICE_ALERT => 'Invoice Alert',
            self::NEW_EMAIL => 'New Email',
        };
    }
}

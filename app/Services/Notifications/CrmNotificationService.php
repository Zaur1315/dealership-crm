<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\CrmNotificationType;
use App\Enums\TaskStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\CrmNotification;
use App\Models\Dealership;
use App\Models\Email;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CrmNotificationService
{
    public function notifyUser(
        User $recipient,
        CrmNotificationType $type,
        string $title,
        ?string $body = null,
        ?Dealership $dealership = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetUrl = null,
        array $payload = [],
    ): CrmNotification {
        return CrmNotification::query()->create([
            'dealership_id' => $dealership?->id,
            'recipient_user_id' => $recipient->id,
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_url' => $targetUrl,
            'payload' => $payload,
            'expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * @param  Collection<int, User>  $recipients
     */
    public function notifyUsers(
        Collection $recipients,
        CrmNotificationType $type,
        string $title,
        ?string $body = null,
        ?Dealership $dealership = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetUrl = null,
        array $payload = [],
    ): void {
        foreach ($recipients as $recipient) {
            $this->notifyUser(
                recipient: $recipient,
                type: $type,
                title: $title,
                body: $body,
                dealership: $dealership,
                targetType: $targetType,
                targetId: $targetId,
                targetUrl: $targetUrl,
                payload: $payload,
            );
        }
    }

    public function notifyNewLead(Lead $lead): void
    {
        $dealership = $lead->dealership;

        if (! $dealership instanceof Dealership) {
            return;
        }

        $recipients = $this->resolveRecipients(
            dealership: $dealership,
            roles: [
                User::ROLE_GM,
                User::ROLE_MANAGER,
                User::ROLE_SALESPERSON,
            ],
        );

        $this->notifyUsers(
            recipients: $recipients,
            type: CrmNotificationType::NEW_LEAD,
            title: 'New lead created',
            body: $lead->full_name.' was created.',
            dealership: $dealership,
            targetType: Lead::class,
            targetId: $lead->id,
            targetUrl: LeadResource::getUrl('view', ['record' => $lead]),
            payload: [
                'lead_name' => $lead->full_name,
                'lead_email' => $lead->email,
            ],
        );
    }

    public function notifyTaskExpired(Task $task): void
    {
        $dealership = $task->dealership;

        if (! $dealership instanceof Dealership) {
            return;
        }

        $recipients = $this->resolveRecipients(
            dealership: $dealership,
            roles: [
                User::ROLE_GM,
                User::ROLE_MANAGER,
            ],
        );

        $this->notifyUsers(
            recipients: $recipients,
            type: CrmNotificationType::TASK_EXPIRED,
            title: 'Task expired',
            body: $task->title.' is overdue.',
            dealership: $dealership,
            targetType: Task::class,
            targetId: $task->id,
            targetUrl: TaskResource::getUrl('view', ['record' => $task]),
            payload: [
                'task_title' => $task->title,
                'lead_id' => $task->lead_id,
            ],
        );
    }

    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    private function resolveRecipients(Dealership $dealership, array $roles): Collection
    {
        /** @var Collection<int, User> $recipients */
        $recipients = $dealership->users()
            ->where('status', User::STATUS_ACTIVE)
            ->whereIn('role', $roles)
            ->get();

        if ($recipients->isNotEmpty()) {
            return $recipients;
        }

        /** @var Collection<int, User> $fallbackRecipients */
        $fallbackRecipients = User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->whereIn('role', $roles)
            ->get();

        return $fallbackRecipients;
    }

    public function notifyTaskDue(Task $task): void
    {
        $dealership = $task->dealership;

        if (! $dealership instanceof Dealership) {
            return;
        }

        $exists = CrmNotification::query()
            ->where('type', CrmNotificationType::TASK_DUE->value)
            ->where('target_type', Task::class)
            ->where('target_id', $task->id)
            ->exists();

        if ($exists) {
            return;
        }

        $recipients = $this->resolveRecipients(
            dealership: $dealership,
            roles: [
                User::ROLE_GM,
                User::ROLE_MANAGER,
                User::ROLE_SALESPERSON,
            ],
        );

        $this->notifyUsers(
            recipients: $recipients,
            type: CrmNotificationType::TASK_DUE,
            title: 'Task is due',
            body: $task->title.' is overdue.',
            dealership: $dealership,
            targetType: Task::class,
            targetId: $task->id,
            targetUrl: TaskResource::getUrl('view', ['record' => $task]),
            payload: [
                'task_title' => $task->title,
                'lead_id' => $task->lead_id,
                'due_at' => $this->dateTimeValue($task->getAttribute('due_at')),
            ],
        );
    }

    private function dateTimeValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }

    public function notifyOverdueTasksOnLogin(User $user): void
    {
        $dealershipIds = $user->isGm()
            ? null
            : $user->dealerships()->pluck('dealerships.id')->all();

        $query = Task::query()
            ->where('status', TaskStatus::ACTIVE->value)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now());

        if (is_array($dealershipIds)) {
            if ($dealershipIds === []) {
                return;
            }

            $query->whereIn('dealership_id', $dealershipIds);
        }

        $count = $query->count();

        if ($count === 0) {
            return;
        }

        $exists = CrmNotification::query()
            ->where('recipient_user_id', $user->id)
            ->where('type', CrmNotificationType::OVERDUE_TASKS_ON_LOGIN->value)
            ->whereNull('read_at')
            ->exists();

        if ($exists) {
            return;
        }

        $this->notifyUser(
            recipient: $user,
            type: CrmNotificationType::OVERDUE_TASKS_ON_LOGIN,
            title: 'Overdue tasks',
            body: "You have {$count} overdue task(s).",
            targetUrl: TaskResource::getUrl('index'),
            payload: [
                'overdue_tasks_count' => $count,
            ],
        );
    }

    public function notifyNewEmail(Email $email): void
    {
        $dealership = $email->dealership;

        if (! $dealership instanceof Dealership) {
            return;
        }

        $exists = CrmNotification::query()
            ->where('type', CrmNotificationType::NEW_EMAIL->value)
            ->where('target_type', Email::class)
            ->where('target_id', $email->id)
            ->exists();

        if ($exists) {
            return;
        }

        $recipients = $this->resolveRecipients(
            dealership: $dealership,
            roles: [
                User::ROLE_GM,
                User::ROLE_MANAGER,
                User::ROLE_SALESPERSON,
            ],
        );

        $this->notifyUsers(
            recipients: $recipients,
            type: CrmNotificationType::NEW_EMAIL,
            title: 'New email received',
            body: trim(($email->from_email ?? 'Unknown sender').' - '.($email->subject ?? '(No subject)')),
            dealership: $dealership,
            targetType: Email::class,
            targetId: $email->id,
            targetUrl: EmailResource::getUrl('view', ['record' => $email]),
            payload: [
                'from_email' => $email->from_email,
                'subject' => $email->subject,
                'needs_manual_review' => $email->needs_manual_review,
            ],
        );
    }

    public function notifyInvoiceAlert(Email $email): void
    {
        $dealership = $email->dealership;

        if (! $dealership instanceof Dealership) {
            return;
        }

        $recipients = $this->resolveRecipients(
            dealership: $dealership,
            roles: [
                User::ROLE_GM,
                User::ROLE_MANAGER,
            ],
        );

        $this->notifyUsers(
            recipients: $recipients,
            type: CrmNotificationType::INVOICE_ALERT,
            title: 'Invoice email alert',
            body: 'Outgoing email contains invoice keyword and attachment.',
            dealership: $dealership,
            targetType: Email::class,
            targetId: $email->id,
            targetUrl: EmailResource::getUrl('view', ['record' => $email]),
            payload: [
                'subject' => $email->subject,
                'body_text' => $email->body_text,
                'from_email' => $email->from_email,
                'to' => $email->to,
                'has_attachments' => true,
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\CrmNotificationType;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\CrmNotification;
use App\Models\Dealership;
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
}

<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Enums\AuditLogAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function record(
        AuditLogAction $action,
        Model $entity,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null,
    ): AuditLog {
        $user ??= Auth::user() instanceof User ? Auth::user() : null;

        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'user_name' => $user?->full_name,
            'action' => $action->value,
            'entity_type' => $entity::class,
            'entity_id' => $this->entityId($entity),
            'entity_label' => $this->entityLabel($entity),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function created(Model $entity, ?User $user = null): AuditLog
    {
        return $this->record(
            action: AuditLogAction::CREATED,
            entity: $entity,
            oldValues: null,
            newValues: $this->modelValues($entity),
            user: $user,
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function updated(
        Model $entity,
        array $oldValues,
        array $newValues,
        ?User $user = null,
    ): AuditLog {
        return $this->record(
            action: AuditLogAction::UPDATED,
            entity: $entity,
            oldValues: $oldValues,
            newValues: $newValues,
            user: $user,
        );
    }

    public function deleted(Model $entity, ?User $user = null): AuditLog
    {
        return $this->record(
            action: AuditLogAction::DELETED,
            entity: $entity,
            oldValues: $this->modelValues($entity),
            newValues: null,
            user: $user,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function modelValues(Model $entity): array
    {
        return $this->normalizeValues($entity->getAttributes());
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalizeValues(array $values): array
    {
        unset($values['password']);
        unset($values['remember_token']);

        foreach ($values as $key => $value) {
            if ($value instanceof \BackedEnum) {
                $values[$key] = $value->value;

                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $values[$key] = $value->format('Y-m-d H:i:s');

                continue;
            }

            if (is_array($value)) {
                $values[$key] = $this->normalizeValues($value);

                continue;
            }

            if (is_object($value)) {
                $values[$key] = (string) $value;
            }
        }

        return $values;
    }

    private function entityId(Model $entity): ?int
    {
        $key = $entity->getKey();

        return is_numeric($key) ? (int) $key : null;
    }

    private function entityLabel(Model $entity): ?string
    {
        foreach (['full_name', 'name', 'title', 'email', 'username'] as $field) {
            $value = $entity->getAttribute($field);

            if (is_scalar($value) && $value !== '') {
                return (string) $value;
            }
        }

        $key = $entity->getKey();

        return $key === null ? null : '#'.$key;
    }
}

<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\Audit\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        app(AuditLogService::class)->created($model);
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($model->getChanges() as $field => $newValue) {
            if (in_array($field, $this->ignoredFields(), true)) {
                continue;
            }

            $oldValues[$field] = $model->getOriginal($field);
            $newValues[$field] = $newValue;
        }

        if ($oldValues === [] && $newValues === []) {
            return;
        }

        app(AuditLogService::class)->updated(
            entity: $model,
            oldValues: app(AuditLogService::class)->normalizeValues($oldValues),
            newValues: app(AuditLogService::class)->normalizeValues($newValues),
        );
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        app(AuditLogService::class)->deleted($model);
    }

    private function shouldSkip(Model $model): bool
    {
        return $model instanceof AuditLog;
    }

    /**
     * @return array<int, string>
     */
    private function ignoredFields(): array
    {
        return [
            'updated_at',
            'remember_token',
            'password',
        ];
    }
}

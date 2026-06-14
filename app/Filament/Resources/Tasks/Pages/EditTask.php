<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\Leads\LeadActivityService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof Task) {
            return parent::handleRecordUpdate($record, $data);
        }

        $oldValues = $this->trackedValues($record);

        $record = parent::handleRecordUpdate($record, $data);

        if (! $record instanceof Task) {
            return $record;
        }

        $newValues = $this->trackedValues($record);

        if ($oldValues !== $newValues) {
            $user = Auth::user();

            app(LeadActivityService::class)->taskUpdated(
                task: $record,
                oldValues: $oldValues,
                newValues: $newValues,
                user: $user instanceof User ? $user : null,
            );
        }

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    private function trackedValues(Task $task): array
    {
        return [
            'title' => $task->title,
            'description' => $task->description,
            'type' => $this->attributeToString($task->getAttribute('type')),
            'status' => $this->attributeToString($task->getAttribute('status')),
            'due_at' => $this->attributeToString($task->getAttribute('due_at')),
            'lead_id' => $task->lead_id,
        ];
    }

    private function attributeToString(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

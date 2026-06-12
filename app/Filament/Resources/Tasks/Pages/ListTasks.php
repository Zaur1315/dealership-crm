<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'due_today' => Tab::make('Due Today')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->whereDate('due_at', today())
                    ->where('status', TaskStatus::ACTIVE->value)),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', TaskStatus::ACTIVE->value)
                    ->where(function (Builder $query): void {
                        $query
                            ->whereNull('due_at')
                            ->orWhere('due_at', '>=', now());
                    })),

            'expired' => Tab::make('Expired')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', TaskStatus::EXPIRED->value)),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query
                    ->where('status', TaskStatus::COMPLETED->value)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'due_today';
    }
}

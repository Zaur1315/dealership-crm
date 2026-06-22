<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_lead')
                ->label('Open Lead')
                ->icon('heroicon-o-user')
                ->color('primary')
                ->visible(fn (): bool => $this->hasLinkedLead())
                ->url(fn (): string => $this->linkedLeadUrl())
                ->openUrlInNewTab(false),
        ];
    }

    private function hasLinkedLead(): bool
    {
        $record = $this->getRecord();

        return $record instanceof Task && $record->lead_id !== null;
    }

    private function linkedLeadUrl(): string
    {
        $record = $this->getRecord();

        if (! $record instanceof Task || $record->lead_id === null) {
            return '#';
        }

        return LeadResource::getUrl('view', [
            'record' => $record->lead_id,
        ]);
    }
}

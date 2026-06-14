<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadPipelineService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $pipelineStage = $data['pipeline_stage'] ?? null;

        unset($data['pipeline_stage']);

        $record = parent::handleRecordUpdate($record, $data);

        $user = Auth::user();

        if (! $record instanceof Lead) {
            return $record;
        }

        if ($pipelineStage === null) {
            return $record;
        }

        app(LeadPipelineService::class)->moveToStage(
            lead: $record,
            stage: (string) $pipelineStage,
            changedBy: $user instanceof User ? $user : null,
        );

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),

            Action::make('emailThread')
                ->label('Email Thread')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->disabled()
                ->tooltip('Email module will be implemented in a later stage.'),

            Action::make('callLog')
                ->label('Call Log')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->disabled()
                ->tooltip('Call logging will be added with a future Twilio integration.'),
        ];
    }
}

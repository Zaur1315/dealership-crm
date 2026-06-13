<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadPipelineStage;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn (): int => $this->getAllCount()),

            'new' => $this->makeStageTab(LeadPipelineStage::NEW),
            'in_communication' => $this->makeStageTab(LeadPipelineStage::IN_COMMUNICATION),
            'did_not_answer' => $this->makeStageTab(LeadPipelineStage::DID_NOT_ANSWER),
            'in_negotiation' => $this->makeStageTab(LeadPipelineStage::IN_NEGOTIATION),
            'contract' => $this->makeStageTab(LeadPipelineStage::CONTRACT),
            'invoice' => $this->makeStageTab(LeadPipelineStage::INVOICE),
            'won' => $this->makeStageTab(LeadPipelineStage::WON),
            'lost' => $this->makeStageTab(LeadPipelineStage::LOST),
            'not_interested' => $this->makeStageTab(LeadPipelineStage::NOT_INTERESTED),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'new';
    }

    private function makeStageTab(LeadPipelineStage $stage): Tab
    {
        return Tab::make($stage->label())
            ->badge(fn (): int => $this->getStageCount($stage))
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('pipeline_stage', $stage->value));
    }

    private function getAllCount(): int
    {
        return Lead::query()
            ->where('dealership_id', $this->getCurrentDealershipId())
            ->count();
    }

    private function getStageCount(LeadPipelineStage $stage): int
    {
        return Lead::query()
            ->where('dealership_id', $this->getCurrentDealershipId())
            ->where('pipeline_stage', $stage->value)
            ->count();
    }

    private function getCurrentDealershipId(): int
    {
        return app(CurrentDealershipContext::class)->ensureSelected()->id;
    }
}

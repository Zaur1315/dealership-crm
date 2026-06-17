<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Enums\LeadPipelineStage;
use App\Enums\TaskStatus;
use App\Models\Lead;
use App\Models\Task;
use Carbon\CarbonInterface;

class StatisticsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $totalLeads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->whereBetween('created_at', [$from, $until])
            ->count();

        $wonDeals = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::WON->value)
            ->whereBetween('won_at', [$from, $until])
            ->count();

        $revenue = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::WON->value)
            ->whereBetween('won_at', [$from, $until])
            ->sum('deal_value');

        $expiredTasks = Task::query()
            ->where('dealership_id', $dealershipId)
            ->where('status', TaskStatus::EXPIRED->value)
            ->whereBetween('expired_at', [$from, $until])
            ->count();

        return [
            'total_leads' => $totalLeads,
            'won_deals' => $wonDeals,
            'conversion_rate' => $totalLeads > 0 ? round(($wonDeals / $totalLeads) * 100, 2) : 0,
            'revenue' => (float) $revenue,
            'expired_tasks' => $expiredTasks,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function pipelineBreakdown(int $dealershipId): array
    {
        $counts = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->selectRaw('pipeline_stage, count(*) as total')
            ->groupBy('pipeline_stage')
            ->pluck('total', 'pipeline_stage');

        return collect(LeadPipelineStage::cases())
            ->mapWithKeys(fn (LeadPipelineStage $stage): array => [
                $stage->label() => (int) ($counts[$stage->value] ?? 0),
            ])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function taskBreakdown(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $counts = Task::query()
            ->where('dealership_id', $dealershipId)
            ->whereBetween('created_at', [$from, $until])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(TaskStatus::cases())
            ->mapWithKeys(fn (TaskStatus $status): array => [
                $status->label() => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }
}

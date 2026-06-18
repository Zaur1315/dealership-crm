<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Enums\EmailDirection;
use App\Enums\LeadPipelineStage;
use App\Enums\TaskStatus;
use App\Models\Email;
use App\Models\Lead;
use App\Models\Task;
use Carbon\CarbonImmutable;
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

    /**
     * @return array<int, array{period: string, new: int, won: int, lost: int, not_interested: int}>
     */
    /**
     * @return array<int, array{period: string, new: int, won: int, lost: int, not_interested: int}>
     */
    public function leadsOverTime(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $timeline = $this->emptyLeadsTimeline($from, $until);

        $newLeads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->whereBetween('created_at', [$from, $until])
            ->get(['created_at']);

        foreach ($newLeads as $lead) {
            $key = $this->dateKey($lead->created_at);

            if (isset($timeline[$key])) {
                $timeline[$key]['new']++;
            }
        }

        $wonLeads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::WON->value)
            ->whereBetween('won_at', [$from, $until])
            ->get(['won_at']);

        foreach ($wonLeads as $lead) {
            $key = $this->dateKey($lead->won_at);

            if (isset($timeline[$key])) {
                $timeline[$key]['won']++;
            }
        }

        $lostLeads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::LOST->value)
            ->whereBetween('updated_at', [$from, $until])
            ->get(['updated_at']);

        foreach ($lostLeads as $lead) {
            $key = $this->dateKey($lead->updated_at);

            if (isset($timeline[$key])) {
                $timeline[$key]['lost']++;
            }
        }

        $notInterestedLeads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::NOT_INTERESTED->value)
            ->whereBetween('updated_at', [$from, $until])
            ->get(['updated_at']);

        foreach ($notInterestedLeads as $lead) {
            $key = $this->dateKey($lead->updated_at);

            if (isset($timeline[$key])) {
                $timeline[$key]['not_interested']++;
            }
        }

        return array_values($timeline);
    }

    /**
     * @return array<int, array{period: string, revenue: float}>
     */
    public function revenueOverTime(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $timeline = $this->emptyRevenueTimeline($from, $until);

        $leads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->where('pipeline_stage', LeadPipelineStage::WON->value)
            ->whereBetween('won_at', [$from, $until])
            ->get(['won_at', 'deal_value']);

        foreach ($leads as $lead) {
            $key = $this->dateKey($lead->won_at);

            if (isset($timeline[$key])) {
                $timeline[$key]['revenue'] += (float) ($lead->deal_value ?? 0);
            }
        }

        return array_values($timeline);
    }

    /**
     * @return array<string, int|float>
     */
    public function taskCompletionStats(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $completed = Task::query()
            ->where('dealership_id', $dealershipId)
            ->where('status', TaskStatus::COMPLETED->value)
            ->whereBetween('completed_at', [$from, $until])
            ->count();

        $expired = Task::query()
            ->where('dealership_id', $dealershipId)
            ->where('status', TaskStatus::EXPIRED->value)
            ->whereBetween('expired_at', [$from, $until])
            ->count();

        $active = Task::query()
            ->where('dealership_id', $dealershipId)
            ->where('status', TaskStatus::ACTIVE->value)
            ->whereBetween('created_at', [$from, $until])
            ->count();

        $total = $completed + $expired + $active;

        return [
            'completed' => $completed,
            'expired' => $expired,
            'active' => $active,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'expired_rate' => $total > 0 ? round(($expired / $total) * 100, 2) : 0,
        ];
    }

    public function averageResponseTimeMinutes(int $dealershipId, CarbonInterface $from, CarbonInterface $until): ?float
    {
        $leads = Lead::query()
            ->where('dealership_id', $dealershipId)
            ->whereNotNull('first_communication_at')
            ->whereBetween('created_at', [$from, $until])
            ->get(['created_at', 'first_communication_at']);

        if ($leads->isEmpty()) {
            return null;
        }

        $totalMinutes = 0;

        foreach ($leads as $lead) {
            $createdAt = CarbonImmutable::parse((string) $lead->created_at);
            $firstCommunicationAt = CarbonImmutable::parse((string) $lead->first_communication_at);

            $totalMinutes += max(0, $createdAt->diffInMinutes($firstCommunicationAt));
        }

        return round($totalMinutes / $leads->count(), 2);
    }

    /**
     * @return array<string, int>
     */
    public function emailActivity(int $dealershipId, CarbonInterface $from, CarbonInterface $until): array
    {
        $sent = Email::query()
            ->where('dealership_id', $dealershipId)
            ->where('direction', EmailDirection::OUTBOUND->value)
            ->whereBetween('created_at', [$from, $until])
            ->count();

        $received = Email::query()
            ->where('dealership_id', $dealershipId)
            ->where('direction', EmailDirection::INBOUND->value)
            ->whereBetween('created_at', [$from, $until])
            ->count();

        return [
            'sent' => $sent,
            'received' => $received,
        ];
    }

    /**
     * @return array<string, array{period: string, new: int, won: int, lost: int, not_interested: int}>
     */
    private function emptyLeadsTimeline(CarbonInterface $from, CarbonInterface $until): array
    {
        $timeline = [];

        $cursor = CarbonImmutable::parse($from->toDateString())->startOfDay();
        $end = CarbonImmutable::parse($until->toDateString())->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $timeline[$cursor->toDateString()] = [
                'period' => $cursor->format('M d'),
                'new' => 0,
                'won' => 0,
                'lost' => 0,
                'not_interested' => 0,
            ];

            $cursor = $cursor->addDay();
        }

        return $timeline;
    }

    /**
     * @return array<string, array{period: string, revenue: float}>
     */
    private function emptyRevenueTimeline(CarbonInterface $from, CarbonInterface $until): array
    {
        $timeline = [];

        $cursor = CarbonImmutable::parse($from->toDateString())->startOfDay();
        $end = CarbonImmutable::parse($until->toDateString())->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $timeline[$cursor->toDateString()] = [
                'period' => $cursor->format('M d'),
                'revenue' => 0.0,
            ];

            $cursor = $cursor->addDay();
        }

        return $timeline;
    }

    private function dateKey(mixed $date): string
    {
        return CarbonImmutable::parse((string) $date)->toDateString();
    }
}

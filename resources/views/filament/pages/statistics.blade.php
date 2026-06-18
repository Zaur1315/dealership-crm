<x-filament-panels::page>
    <style>
        .crm-stats-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .crm-stats-grid {
            display: grid;
            gap: 16px;
        }

        .crm-stats-grid-5 {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .crm-stats-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .crm-stats-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .crm-card {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 18px;
            background: rgba(24, 24, 27, 0.86);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
            overflow: hidden;
        }

        .crm-card-body {
            padding: 22px;
        }

        .crm-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .crm-card-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #f8fafc;
        }

        .crm-card-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #94a3b8;
        }

        .crm-kpi-label {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .crm-kpi-value {
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.04em;
        }

        .crm-kpi-footnote {
            margin-top: 12px;
            font-size: 12px;
            color: #64748b;
        }

        .crm-progress-track {
            height: 9px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.18);
        }

        .crm-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #06b6d4, #14b8a6);
        }

        .crm-progress-fill-success {
            background: linear-gradient(90deg, #22c55e, #84cc16);
        }

        .crm-progress-fill-danger {
            background: linear-gradient(90deg, #ef4444, #f97316);
        }

        .crm-progress-fill-warning {
            background: linear-gradient(90deg, #f59e0b, #eab308);
        }

        .crm-bar-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .crm-bar-row {
            display: grid;
            grid-template-columns: minmax(120px, 180px) 1fr 52px;
            gap: 14px;
            align-items: center;
        }

        .crm-bar-label {
            font-size: 13px;
            color: #cbd5e1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .crm-bar-value {
            text-align: right;
            font-size: 13px;
            font-weight: 700;
            color: #f8fafc;
        }

        .crm-mini-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .crm-mini-box {
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.56);
            border: 1px solid rgba(148, 163, 184, 0.14);
            padding: 16px;
        }

        .crm-mini-label {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 8px;
        }

        .crm-mini-value {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
        }

        .crm-chart-scroll {
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .crm-column-chart {
            min-width: 720px;
            height: 260px;
            display: grid;
            grid-template-columns: repeat(var(--columns), minmax(32px, 1fr));
            gap: 10px;
            align-items: end;
            padding-top: 12px;
        }

        .crm-column-item {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-width: 32px;
            height: 240px;
        }

        .crm-column-bars {
            flex: 1;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 3px;
        }

        .crm-column-stack {
            width: 18px;
            min-height: 3px;
            border-radius: 8px 8px 3px 3px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.16);
            display: flex;
            flex-direction: column-reverse;
        }

        .crm-column-segment-new {
            background: #06b6d4;
        }

        .crm-column-segment-won {
            background: #22c55e;
        }

        .crm-column-segment-lost {
            background: #ef4444;
        }

        .crm-column-segment-ni {
            background: #f59e0b;
        }

        .crm-revenue-bar {
            width: 24px;
            min-height: 3px;
            border-radius: 8px 8px 3px 3px;
            background: linear-gradient(180deg, #22c55e, #15803d);
        }

        .crm-column-label {
            margin-top: 10px;
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            white-space: nowrap;
        }

        .crm-column-value {
            margin-bottom: 8px;
            font-size: 11px;
            color: #cbd5e1;
            text-align: center;
            min-height: 16px;
        }

        .crm-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .crm-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: #94a3b8;
        }

        .crm-legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
        }

        .crm-empty {
            border: 1px dashed rgba(148, 163, 184, 0.22);
            border-radius: 14px;
            padding: 24px;
            color: #94a3b8;
            font-size: 14px;
            text-align: center;
        }

        @media (max-width: 1280px) {
            .crm-stats-grid-5 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .crm-stats-grid-3,
            .crm-stats-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .crm-stats-grid-5,
            .crm-mini-metrics {
                grid-template-columns: 1fr;
            }

            .crm-bar-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .crm-bar-value {
                text-align: left;
            }
        }
    </style>

    @php
        $stats = $this->getStatistics();

        $summary = $stats['summary'];
        $pipeline = $stats['pipeline'];
        $tasks = $stats['tasks'];
        $leadsOverTime = $stats['leads_over_time'];
        $revenueOverTime = $stats['revenue_over_time'];
        $taskCompletion = $stats['task_completion'];
        $averageResponseTimeMinutes = $stats['average_response_time_minutes'];
        $emailActivity = $stats['email_activity'];

        $formatMinutes = function (?float $minutes): string {
            if ($minutes === null) {
                return 'N/A';
            }

            if ($minutes < 60) {
                return number_format($minutes, 1) . ' min';
            }

            return number_format($minutes / 60, 1) . ' hr';
        };

        $pipelineMax = max(1, (int) collect($pipeline)->max());
        $tasksMax = max(1, (int) collect($tasks)->max());

        $leadsTimelineMax = max(1, (int) collect($leadsOverTime)
            ->map(fn (array $row): int => (int) $row['new'] + (int) $row['won'] + (int) $row['lost'] + (int) $row['not_interested'])
            ->max());

        $revenueTimelineMax = max(1, (float) collect($revenueOverTime)
            ->map(fn (array $row): float => (float) $row['revenue'])
            ->max());

        $hasLeadTimelineData = collect($leadsOverTime)
            ->contains(fn (array $row): bool => ((int) $row['new'] + (int) $row['won'] + (int) $row['lost'] + (int) $row['not_interested']) > 0);

        $hasRevenueTimelineData = collect($revenueOverTime)
            ->contains(fn (array $row): bool => (float) $row['revenue'] > 0);
    @endphp

    <div class="crm-stats-page">
        {{ $this->form }}

        <div class="crm-stats-grid crm-stats-grid-5">
            <div class="crm-card">
                <div class="crm-card-body">
                    <div class="crm-kpi-label">Total Leads</div>
                    <div class="crm-kpi-value">{{ $summary['total_leads'] }}</div>
                    <div class="crm-kpi-footnote">Created in selected period</div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-body">
                    <div class="crm-kpi-label">Won Deals</div>
                    <div class="crm-kpi-value">{{ $summary['won_deals'] }}</div>
                    <div class="crm-kpi-footnote">Closed successfully</div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-body">
                    <div class="crm-kpi-label">Conversion</div>
                    <div class="crm-kpi-value">{{ $summary['conversion_rate'] }}%</div>
                    <div style="margin-top: 14px;">
                        <div class="crm-progress-track">
                            <div class="crm-progress-fill"
                                 style="width: {{ min(100, (float) $summary['conversion_rate']) }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-body">
                    <div class="crm-kpi-label">Revenue</div>
                    <div class="crm-kpi-value">${{ number_format($summary['revenue'], 2) }}</div>
                    <div class="crm-kpi-footnote">Won deal value</div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-body">
                    <div class="crm-kpi-label">Expired Tasks</div>
                    <div class="crm-kpi-value">{{ $summary['expired_tasks'] }}</div>
                    <div class="crm-kpi-footnote">Needs attention</div>
                </div>
            </div>
        </div>

        <div class="crm-stats-grid crm-stats-grid-3">
            <div class="crm-card">
                <div class="crm-card-header">
                    <div>
                        <h3 class="crm-card-title">Average Response Time</h3>
                        <div class="crm-card-subtitle">From lead creation to first communication</div>
                    </div>
                </div>
                <div class="crm-card-body">
                    <div class="crm-kpi-value">{{ $formatMinutes($averageResponseTimeMinutes) }}</div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-header">
                    <div>
                        <h3 class="crm-card-title">Email Activity</h3>
                        <div class="crm-card-subtitle">Sent and received emails</div>
                    </div>
                </div>
                <div class="crm-card-body">
                    <div class="crm-mini-metrics">
                        <div class="crm-mini-box">
                            <div class="crm-mini-label">Sent</div>
                            <div class="crm-mini-value">{{ $emailActivity['sent'] }}</div>
                        </div>
                        <div class="crm-mini-box">
                            <div class="crm-mini-label">Received</div>
                            <div class="crm-mini-value">{{ $emailActivity['received'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-header">
                    <div>
                        <h3 class="crm-card-title">Task Completion</h3>
                        <div class="crm-card-subtitle">Completed vs expired rate</div>
                    </div>
                </div>
                <div class="crm-card-body">
                    <div class="crm-bar-list">
                        <div>
                            <div class="crm-bar-row">
                                <div class="crm-bar-label">Completed</div>
                                <div class="crm-progress-track">
                                    <div class="crm-progress-fill crm-progress-fill-success"
                                         style="width: {{ min(100, (float) $taskCompletion['completion_rate']) }}%;"></div>
                                </div>
                                <div class="crm-bar-value">{{ $taskCompletion['completion_rate'] }}%</div>
                            </div>
                        </div>

                        <div>
                            <div class="crm-bar-row">
                                <div class="crm-bar-label">Expired</div>
                                <div class="crm-progress-track">
                                    <div class="crm-progress-fill crm-progress-fill-danger"
                                         style="width: {{ min(100, (float) $taskCompletion['expired_rate']) }}%;"></div>
                                </div>
                                <div class="crm-bar-value">{{ $taskCompletion['expired_rate'] }}%</div>
                            </div>
                        </div>

                        <div class="crm-mini-box">
                            <div class="crm-mini-label">Active Tasks</div>
                            <div class="crm-mini-value">{{ $taskCompletion['active'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="crm-stats-grid crm-stats-grid-2">
            <div class="crm-card">
                <div class="crm-card-header">
                    <div>
                        <h3 class="crm-card-title">Pipeline Breakdown</h3>
                        <div class="crm-card-subtitle">Current leads by stage</div>
                    </div>
                </div>
                <div class="crm-card-body">
                    <div class="crm-bar-list">
                        @foreach ($pipeline as $stage => $count)
                            @php
                                $percent = ((int) $count / $pipelineMax) * 100;
                            @endphp

                            <div class="crm-bar-row">
                                <div class="crm-bar-label">{{ $stage }}</div>
                                <div class="crm-progress-track">
                                    <div class="crm-progress-fill" style="width: {{ $percent }}%;"></div>
                                </div>
                                <div class="crm-bar-value">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="crm-card">
                <div class="crm-card-header">
                    <div>
                        <h3 class="crm-card-title">Task Breakdown</h3>
                        <div class="crm-card-subtitle">Tasks by current status</div>
                    </div>
                </div>
                <div class="crm-card-body">
                    <div class="crm-bar-list">
                        @foreach ($tasks as $status => $count)
                            @php
                                $percent = ((int) $count / $tasksMax) * 100;
                            @endphp

                            <div class="crm-bar-row">
                                <div class="crm-bar-label">{{ $status }}</div>
                                <div class="crm-progress-track">
                                    <div class="crm-progress-fill crm-progress-fill-warning"
                                         style="width: {{ $percent }}%;"></div>
                                </div>
                                <div class="crm-bar-value">{{ $count }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="crm-card">
            <div class="crm-card-header">
                <div>
                    <h3 class="crm-card-title">Leads Over Time</h3>
                    <div class="crm-card-subtitle">New, won, lost and not interested leads by day</div>
                </div>
            </div>
            <div class="crm-card-body">
                @if (! $hasLeadTimelineData)
                    <div class="crm-empty">No lead activity for the selected period.</div>
                @else
                    <div class="crm-chart-scroll">
                        <div class="crm-column-chart" style="--columns: {{ count($leadsOverTime) }};">
                            @foreach ($leadsOverTime as $row)
                                @php
                                    $new = (int) $row['new'];
                                    $won = (int) $row['won'];
                                    $lost = (int) $row['lost'];
                                    $notInterested = (int) $row['not_interested'];
                                    $total = $new + $won + $lost + $notInterested;
                                    $height = max(4, ($total / $leadsTimelineMax) * 190);
                                @endphp

                                <div class="crm-column-item">
                                    <div class="crm-column-value">{{ $total > 0 ? $total : '' }}</div>
                                    <div class="crm-column-bars">
                                        <div class="crm-column-stack" style="height: {{ $height }}px;">
                                            @if ($new > 0)
                                                <div class="crm-column-segment-new"
                                                     style="height: {{ max(4, ($new / max(1, $total)) * 100) }}%;"></div>
                                            @endif
                                            @if ($won > 0)
                                                <div class="crm-column-segment-won"
                                                     style="height: {{ max(4, ($won / max(1, $total)) * 100) }}%;"></div>
                                            @endif
                                            @if ($lost > 0)
                                                <div class="crm-column-segment-lost"
                                                     style="height: {{ max(4, ($lost / max(1, $total)) * 100) }}%;"></div>
                                            @endif
                                            @if ($notInterested > 0)
                                                <div class="crm-column-segment-ni"
                                                     style="height: {{ max(4, ($notInterested / max(1, $total)) * 100) }}%;"></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="crm-column-label">{{ $row['period'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="crm-legend">
                        <div class="crm-legend-item"><span class="crm-legend-dot" style="background: #06b6d4;"></span>
                            New
                        </div>
                        <div class="crm-legend-item"><span class="crm-legend-dot" style="background: #22c55e;"></span>
                            Won
                        </div>
                        <div class="crm-legend-item"><span class="crm-legend-dot" style="background: #ef4444;"></span>
                            Lost
                        </div>
                        <div class="crm-legend-item"><span class="crm-legend-dot" style="background: #f59e0b;"></span>
                            Not Interested
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="crm-card">
            <div class="crm-card-header">
                <div>
                    <h3 class="crm-card-title">Revenue Over Time</h3>
                    <div class="crm-card-subtitle">Revenue from won deals by day</div>
                </div>
            </div>
            <div class="crm-card-body">
                @if (! $hasRevenueTimelineData)
                    <div class="crm-empty">No revenue for the selected period.</div>
                @else
                    <div class="crm-chart-scroll">
                        <div class="crm-column-chart" style="--columns: {{ count($revenueOverTime) }};">
                            @foreach ($revenueOverTime as $row)
                                @php
                                    $revenue = (float) $row['revenue'];
                                    $height = max(4, ($revenue / $revenueTimelineMax) * 190);
                                @endphp

                                <div class="crm-column-item">
                                    <div
                                        class="crm-column-value">{{ $revenue > 0 ? '$' . number_format($revenue, 0) : '' }}</div>
                                    <div class="crm-column-bars">
                                        <div class="crm-revenue-bar" style="height: {{ $height }}px;"></div>
                                    </div>
                                    <div class="crm-column-label">{{ $row['period'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>

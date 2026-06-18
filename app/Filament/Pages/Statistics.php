<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Statistics\StatisticsService;
use App\Support\Dealership\CurrentDealershipContext;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Statistics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistics';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.pages.statistics';

    public string $period = 'month';

    public ?string $date_from = null;

    public ?string $date_until = null;

    public function mount(): void
    {
        $this->applyPeriod($this->period);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isManagerOrGm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filters')
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->options([
                                'today' => 'Daily',
                                'week' => 'Weekly',
                                'month' => 'This month',
                                'custom' => 'Custom range',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (?string $state): null => $this->applyPeriod($state ?? 'custom')),

                        DatePicker::make('date_from')
                            ->label('From')
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->period = 'custom';
                            }),

                        DatePicker::make('date_until')
                            ->label('Until')
                            ->live()
                            ->afterStateUpdated(function (): void {
                                $this->period = 'custom';
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    public function applyPeriod(string $period): null
    {
        $this->period = $period;

        $now = CarbonImmutable::now();

        if ($period === 'today') {
            $this->date_from = $now->startOfDay()->toDateString();
            $this->date_until = $now->endOfDay()->toDateString();

            return null;
        }

        if ($period === 'week') {
            $this->date_from = $now->startOfWeek()->toDateString();
            $this->date_until = $now->endOfDay()->toDateString();

            return null;
        }

        if ($period === 'month') {
            $this->date_from = $now->startOfMonth()->toDateString();
            $this->date_until = $now->endOfDay()->toDateString();

            return null;
        }

        $this->date_from ??= $now->startOfMonth()->toDateString();
        $this->date_until ??= $now->endOfDay()->toDateString();

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        $from = CarbonImmutable::parse($this->date_from ?? now()->startOfMonth()->toDateString())->startOfDay();
        $until = CarbonImmutable::parse($this->date_until ?? now()->toDateString())->endOfDay();

        $service = app(StatisticsService::class);

        return [
            'summary' => $service->summary($dealership->id, $from, $until),
            'pipeline' => $service->pipelineBreakdown($dealership->id),
            'tasks' => $service->taskBreakdown($dealership->id, $from, $until),
            'leads_over_time' => $service->leadsOverTime($dealership->id, $from, $until),
            'revenue_over_time' => $service->revenueOverTime($dealership->id, $from, $until),
            'task_completion' => $service->taskCompletionStats($dealership->id, $from, $until),
            'average_response_time_minutes' => $service->averageResponseTimeMinutes($dealership->id, $from, $until),
            'email_activity' => $service->emailActivity($dealership->id, $from, $until),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('statistics.export-pdf', [
                    'period' => $this->period,
                    'date_from' => $this->date_from,
                    'date_until' => $this->date_until,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}

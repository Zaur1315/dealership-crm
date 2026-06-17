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

    public ?string $date_from = null;

    public ?string $date_until = null;

    public function mount(): void
    {
        $this->date_from ??= now()->startOfMonth()->toDateString();
        $this->date_until ??= now()->endOfDay()->toDateString();
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
                        DatePicker::make('date_from')
                            ->label('From')
                            ->live(),

                        DatePicker::make('date_until')
                            ->label('Until')
                            ->live(),
                    ])
                    ->columns(2),
            ]);
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
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('statistics.export-pdf', [
                    'date_from' => $this->date_from,
                    'date_until' => $this->date_until,
                ]))
                ->openUrlInNewTab(),
        ];
    }
}

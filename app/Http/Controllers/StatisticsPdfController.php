<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Statistics\StatisticsService;
use App\Support\Dealership\CurrentDealershipContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StatisticsPdfController extends Controller
{
    public function __invoke(Request $request, StatisticsService $statisticsService): Response
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isManagerOrGm(), 403);

        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        $from = CarbonImmutable::parse(
            $request->query('date_from', now()->startOfMonth()->toDateString()),
        )->startOfDay();

        $until = CarbonImmutable::parse(
            $request->query('date_until', now()->toDateString()),
        )->endOfDay();

        $data = [
            'dealership' => $dealership,
            'dateFrom' => $from,
            'dateUntil' => $until,
            'summary' => $statisticsService->summary($dealership->id, $from, $until),
            'pipeline' => $statisticsService->pipelineBreakdown($dealership->id),
            'tasks' => $statisticsService->taskBreakdown($dealership->id, $from, $until),
            'leadsOverTime' => $statisticsService->leadsOverTime($dealership->id, $from, $until),
            'revenueOverTime' => $statisticsService->revenueOverTime($dealership->id, $from, $until),
            'taskCompletion' => $statisticsService->taskCompletionStats($dealership->id, $from, $until),
            'averageResponseTimeMinutes' => $statisticsService->averageResponseTimeMinutes($dealership->id, $from, $until),
            'emailActivity' => $statisticsService->emailActivity($dealership->id, $from, $until),
        ];

        return Pdf::loadView('pdf.statistics', $data)
            ->setPaper('a4')
            ->download('statistics-'.$from->toDateString().'-'.$until->toDateString().'.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\WeatherReportAction;
use App\Http\Requests\DashboardPeriodValidationRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const FUKUI_CITY_ID = 2;

    public function __invoke(DashboardPeriodValidationRequest $request, WeatherReportAction $weatherReportAction): View
    {
        $fromDate = $request->fromDate();
        $toDate = $request->toDate();

        $trend = $weatherReportAction->getTrendByPeriod(self::FUKUI_CITY_ID, $fromDate, $toDate);
        $dayStats = $weatherReportAction->getExtremesByPeriod(self::FUKUI_CITY_ID, $fromDate, $toDate);
        $periodLabel = $fromDate->isSameDay($toDate)
            ? $fromDate->toDateString()
            : sprintf('%s 〜 %s', $fromDate->toDateString(), $toDate->toDateString());

        return view('dashboard', [
            'title' => '福井気温ダッシュボード',
            'chartData' => $trend,
            'dayStats' => $dayStats,
            'selectedFrom' => $fromDate->toDateString(),
            'selectedTo' => $toDate->toDateString(),
            'periodLabel' => $periodLabel,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\WeatherReportAction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const FUKUI_CITY_ID = 2;

    public function __invoke(WeatherReportAction $weatherReportAction): View
    {
        $trend = $weatherReportAction->getTodayTrend(self::FUKUI_CITY_ID);

        $todayStats = $weatherReportAction->getTodayExtremes(self::FUKUI_CITY_ID);

        return view('dashboard', [
            'title' => '福井気温ダッシュボード',
            'chartData' => $trend,
            'todayStats' => $todayStats,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\DailyWeatherStatAction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const FUKUI_CITY_ID = 2;

    public function __invoke(DailyWeatherStatAction $dailyWeatherStatAction): View
    {
        $trend = $dailyWeatherStatAction->getMonthlyTrend(self::FUKUI_CITY_ID)
            ->filter(fn ($row) => $row->average_temperature !== null)
            ->map(fn ($row) => [
                'date' => (string) $row->measured_date,
                'average_temperature' => round((float) $row->average_temperature, 1),
            ])
            ->values();

        $todayStat = $dailyWeatherStatAction->getTodayExtremes(self::FUKUI_CITY_ID);

        return view('dashboard', [
            'title' => '福井気温ダッシュボード',
            'chartData' => $trend,
            'todayStats' => $todayStat ? [
                'date' => (string) $todayStat->measured_date,
                'max_temperature' => $todayStat->max_temperature !== null ? round((float) $todayStat->max_temperature, 1) : null,
                'min_temperature' => $todayStat->min_temperature !== null ? round((float) $todayStat->min_temperature, 1) : null,
            ] : null,
        ]);
    }
}

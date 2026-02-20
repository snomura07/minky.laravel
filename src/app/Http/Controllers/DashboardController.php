<?php

namespace App\Http\Controllers;

use App\Actions\WeatherReportAction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const FUKUI_CITY_ID = 2;

    public function __invoke(Request $request, WeatherReportAction $weatherReportAction): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $targetDate = isset($validated['date'])
            ? CarbonImmutable::createFromFormat('Y-m-d', $validated['date'], 'Asia/Tokyo')
            : CarbonImmutable::today('Asia/Tokyo');

        $trend = $weatherReportAction->getTrendByDate(self::FUKUI_CITY_ID, $targetDate);
        $dayStats = $weatherReportAction->getExtremesByDate(self::FUKUI_CITY_ID, $targetDate);

        return view('dashboard', [
            'title' => '福井気温ダッシュボード',
            'chartData' => $trend,
            'dayStats' => $dayStats,
            'selectedDate' => $targetDate->toDateString(),
        ]);
    }
}

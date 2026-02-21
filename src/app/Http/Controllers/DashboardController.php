<?php

namespace App\Http\Controllers;

use App\Actions\WeatherReportAction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const FUKUI_CITY_ID = 2;

    public function __invoke(Request $request, WeatherReportAction $weatherReportAction): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $today = CarbonImmutable::today('Asia/Tokyo');
        $fromInput = $validated['from'] ?? null;
        $toInput = $validated['to'] ?? null;

        if (($fromInput === null || $toInput === null) && isset($validated['date'])) {
            $fromInput ??= $validated['date'];
            $toInput ??= $validated['date'];
        }

        if ($fromInput === null && $toInput === null) {
            $fromInput = $today->toDateString();
            $toInput = $today->toDateString();
        } elseif ($fromInput === null) {
            $fromInput = $toInput;
        } elseif ($toInput === null) {
            $toInput = $fromInput;
        }

        $fromDate = CarbonImmutable::createFromFormat('Y-m-d', $fromInput, 'Asia/Tokyo');
        $toDate = CarbonImmutable::createFromFormat('Y-m-d', $toInput, 'Asia/Tokyo');

        if ($fromDate->gt($toDate)) {
            throw ValidationException::withMessages([
                'to' => '終了日は開始日以降を指定してください。',
            ]);
        }

        if ($fromDate->diffInDays($toDate) > 182) {
            throw ValidationException::withMessages([
                'to' => '指定できる期間は最大183日（約半年）です。',
            ]);
        }

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

<?php

namespace App\Http\Controllers;

use App\Actions\CityAction;
use App\Actions\WeatherReportAction;
use App\Http\Requests\DashboardPeriodValidationRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardPeriodValidationRequest $request,
        WeatherReportAction $weatherReportAction,
        CityAction $cityAction
    ): View
    {
        $cityId = $request->cityId();
        $fromDate = $request->fromDate();
        $toDate = $request->toDate();
        $cities = $cityAction->getAll();
        $selectedCity = $cityAction->findById($cityId);

        $trend = $weatherReportAction->getTrendByPeriod($cityId, $fromDate, $toDate);
        $dayStats = $weatherReportAction->getExtremesByPeriod($cityId, $fromDate, $toDate);
        $periodLabel = $fromDate->isSameDay($toDate)
            ? $fromDate->toDateString()
            : sprintf('%s 〜 %s', $fromDate->toDateString(), $toDate->toDateString());

        return view('dashboard', [
            'title' => sprintf(
                '%s気温ダッシュボード',
                $selectedCity?->city_name ?? '気象'
            ),
            'chartData' => $trend,
            'dayStats' => $dayStats,
            'cities' => $cities,
            'selectedCityId' => $cityId,
            'selectedCityLabel' => $selectedCity
                ? sprintf('%s (%s)', $selectedCity->city_name, $selectedCity->prefecture_name)
                : sprintf('city_id: %d', $cityId),
            'selectedFrom' => $fromDate->toDateString(),
            'selectedTo' => $toDate->toDateString(),
            'selectedYMin' => $request->yMin(),
            'selectedYMax' => $request->yMax(),
            'periodLabel' => $periodLabel,
        ]);
    }
}

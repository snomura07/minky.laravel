<?php

namespace App\Repositories;

use App\Models\DailyWeatherStat;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyWeatherStatRepository
{
    public function upsertByDate(int $cityId, CarbonInterface $date, array $attributes): DailyWeatherStat
    {
        DailyWeatherStat::query()->updateOrCreate(
            [
                'city_id' => $cityId,
                'measured_date' => $date->toDateString(),
            ],
            $attributes
        );

        return $this->findByDate($cityId, $date);
    }

    public function findByDate(int $cityId, CarbonInterface $date): ?DailyWeatherStat
    {
        return DailyWeatherStat::query()
            ->where('city_id', $cityId)
            ->whereDate('measured_date', $date->toDateString())
            ->latest('id')
            ->first();
    }

    public function findLatest(int $cityId): ?DailyWeatherStat
    {
        return DailyWeatherStat::query()
            ->where('city_id', $cityId)
            ->orderByDesc('measured_date')
            ->latest('id')
            ->first();
    }

    public function findMonthlyTrend(int $cityId, CarbonInterface $fromDate, CarbonInterface $toDate): Collection
    {
        return DailyWeatherStat::query()
            ->where('city_id', $cityId)
            ->whereBetween('measured_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderBy('measured_date')
            ->get([
                'measured_date',
                'average_temperature',
            ]);
    }
}

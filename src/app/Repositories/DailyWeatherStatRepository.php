<?php

namespace App\Repositories;

use App\Models\DailyWeatherStat;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyWeatherStatRepository
{
    private const LOCATION_TOLERANCE = 0.01;

    public function upsertByDate(float $latitude, float $longitude, CarbonInterface $date, array $attributes): DailyWeatherStat
    {
        DailyWeatherStat::query()->updateOrCreate(
            [
                'measured_date' => $date->toDateString(),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            $attributes
        );

        return $this->findByDate($latitude, $longitude, $date);
    }

    public function findByDate(float $latitude, float $longitude, CarbonInterface $date): ?DailyWeatherStat
    {
        return DailyWeatherStat::query()
            ->whereBetween('latitude', [$latitude - self::LOCATION_TOLERANCE, $latitude + self::LOCATION_TOLERANCE])
            ->whereBetween('longitude', [$longitude - self::LOCATION_TOLERANCE, $longitude + self::LOCATION_TOLERANCE])
            ->whereDate('measured_date', $date->toDateString())
            ->latest('id')
            ->first();
    }

    public function findLatest(float $latitude, float $longitude): ?DailyWeatherStat
    {
        return DailyWeatherStat::query()
            ->whereBetween('latitude', [$latitude - self::LOCATION_TOLERANCE, $latitude + self::LOCATION_TOLERANCE])
            ->whereBetween('longitude', [$longitude - self::LOCATION_TOLERANCE, $longitude + self::LOCATION_TOLERANCE])
            ->orderByDesc('measured_date')
            ->latest('id')
            ->first();
    }

    public function findMonthlyTrend(float $latitude, float $longitude, CarbonInterface $fromDate, CarbonInterface $toDate): Collection
    {
        return DailyWeatherStat::query()
            ->whereBetween('latitude', [$latitude - self::LOCATION_TOLERANCE, $latitude + self::LOCATION_TOLERANCE])
            ->whereBetween('longitude', [$longitude - self::LOCATION_TOLERANCE, $longitude + self::LOCATION_TOLERANCE])
            ->whereBetween('measured_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->orderBy('measured_date')
            ->get([
                'measured_date',
                'average_temperature',
            ]);
    }
}

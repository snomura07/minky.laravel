<?php

namespace App\Repositories;

use App\Models\WeatherReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WeatherReportRepository
{
    public function create(array $attributes): WeatherReport
    {
        return WeatherReport::query()->create($attributes);
    }

    public function getDailyAggregateByCityId(CarbonInterface $date, int $cityId): ?object
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        return WeatherReport::query()
            ->whereBetween('measured_time', [$start, $end])
            ->where('city_id', $cityId)
            ->selectRaw('AVG(temperature) as avg_temperature,
                AVG(humidity) as avg_humidity,
                AVG(wind_speed) as avg_wind_speed,
                AVG(precipitation) as avg_precipitation,
                MAX(temperature) as max_temperature,
                MIN(temperature) as min_temperature')
            ->first();
    }

    public function findDailyTemperatureTrendByCityId(CarbonInterface $date, int $cityId): Collection
    {
        return $this->findTemperatureTrendByCityId($date, $date, $cityId);
    }

    public function findTemperatureTrendByCityId(
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        int $cityId
    ): Collection {
        $start = $fromDate->copy()->startOfDay();
        $end = $toDate->copy()->endOfDay();

        return WeatherReport::query()
            ->whereBetween('measured_time', [$start, $end])
            ->where('city_id', $cityId)
            ->orderBy('measured_time')
            ->get(['measured_time', 'temperature']);
    }

    public function findDailyTemperatureExtremesByCityId(CarbonInterface $date, int $cityId): ?object
    {
        return $this->findTemperatureExtremesByCityId($date, $date, $cityId);
    }

    public function findTemperatureExtremesByCityId(
        CarbonInterface $fromDate,
        CarbonInterface $toDate,
        int $cityId
    ): ?object {
        $start = $fromDate->copy()->startOfDay();
        $end = $toDate->copy()->endOfDay();

        return WeatherReport::query()
            ->whereBetween('measured_time', [$start, $end])
            ->where('city_id', $cityId)
            ->selectRaw('MAX(temperature) as max_temperature, MIN(temperature) as min_temperature')
            ->first();
    }
}

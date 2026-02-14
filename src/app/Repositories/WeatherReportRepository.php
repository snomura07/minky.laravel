<?php

namespace App\Repositories;

use App\Models\WeatherReport;
use Carbon\CarbonInterface;

class WeatherReportRepository
{
    public function create(array $attributes): WeatherReport
    {
        return WeatherReport::query()->create($attributes);
    }

    public function getDailyAggregate(CarbonInterface $date): ?object
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        return WeatherReport::query()
            ->whereBetween('measured_time', [$start, $end])
            ->selectRaw(
                'AVG(temperature) as avg_temperature,
                AVG(humidity) as avg_humidity,
                AVG(wind_speed) as avg_wind_speed,
                AVG(precipitation) as avg_precipitation,
                MAX(temperature) as max_temperature,
                MIN(temperature) as min_temperature'
            )
            ->first();
    }
}

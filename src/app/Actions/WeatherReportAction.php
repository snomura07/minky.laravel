<?php

namespace App\Actions;

use App\Models\WeatherReport;
use App\Repositories\WeatherReportRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class WeatherReportAction
{
    public function __construct(
        private readonly WeatherReportRepository $weatherReportRepository
    ) {
    }

    public function fetchAndStore(float $latitude, float $longitude): WeatherReport
    {
        $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,precipitation',
            'timezone' => 'Asia/Tokyo',
        ])->throw();

        $current = $response->json('current');

        return $this->weatherReportRepository->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'measured_time' => CarbonImmutable::parse((string) $current['time'], 'Asia/Tokyo'),
            'temperature' => (float) $current['temperature_2m'],
            'humidity' => (float) $current['relative_humidity_2m'],
            'wind_speed' => (float) $current['wind_speed_10m'],
            'precipitation' => (float) $current['precipitation'],
        ]);
    }
}

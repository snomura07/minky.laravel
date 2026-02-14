<?php

namespace App\Actions;

use App\Repositories\CityRepository;
use App\Models\WeatherReport;
use App\Repositories\WeatherReportRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class WeatherReportAction
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly WeatherReportRepository $weatherReportRepository
    ) {
    }

    public function fetchAndStore(int $cityId): WeatherReport
    {
        $city = $this->cityRepository->findById($cityId);
        if ($city === null) {
            throw new \InvalidArgumentException("city_id={$cityId} が cities に存在しません。");
        }

        $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $city->latitude,
            'longitude' => $city->longitude,
            'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,precipitation',
            'timezone' => 'Asia/Tokyo',
        ])->throw();

        $current = $response->json('current');

        return $this->weatherReportRepository->create([
            'city_id' => (int) $city->id,
            'latitude' => (float) $city->latitude,
            'longitude' => (float) $city->longitude,
            'measured_time' => CarbonImmutable::parse((string) $current['time'], 'Asia/Tokyo'),
            'temperature' => (float) $current['temperature_2m'],
            'humidity' => (float) $current['relative_humidity_2m'],
            'wind_speed' => (float) $current['wind_speed_10m'],
            'precipitation' => (float) $current['precipitation'],
        ]);
    }
}

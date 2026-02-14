<?php

namespace App\Actions;

use App\Models\DailyWeatherStat;
use App\Repositories\DailyWeatherStatRepository;
use App\Repositories\WeatherReportRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyWeatherStatAction
{
    public function __construct(
        private readonly WeatherReportRepository $weatherReportRepository,
        private readonly DailyWeatherStatRepository $dailyWeatherStatRepository
    ) {
    }

    public function aggregateAndStore(float $latitude, float $longitude, ?CarbonInterface $date = null): ?DailyWeatherStat
    {
        $targetDate = $date ? CarbonImmutable::instance($date) : CarbonImmutable::today('Asia/Tokyo');
        $aggregate = $this->weatherReportRepository->getDailyAggregate($targetDate);
        if ($aggregate === null || $aggregate->avg_temperature === null) {
            return null;
        }

        return $this->dailyWeatherStatRepository->upsertByDate(
            $latitude,
            $longitude,
            $targetDate,
            [
                'average_temperature' => (float) $aggregate->avg_temperature,
                'average_humidity' => (float) $aggregate->avg_humidity,
                'average_wind_speed' => (float) $aggregate->avg_wind_speed,
                'average_precipitation' => (float) $aggregate->avg_precipitation,
                'max_temperature' => (float) $aggregate->max_temperature,
                'min_temperature' => (float) $aggregate->min_temperature,
            ]
        );
    }

    public function getMonthlyTrend(float $latitude, float $longitude): Collection
    {
        $today = CarbonImmutable::today('Asia/Tokyo');
        $fromDate = $today->subDays(29);

        return $this->dailyWeatherStatRepository->findMonthlyTrend(
            $latitude,
            $longitude,
            $fromDate,
            $today
        );
    }

    public function getTodayExtremes(float $latitude, float $longitude): ?DailyWeatherStat
    {
        $today = CarbonImmutable::today('Asia/Tokyo');
        $todayStat = $this->dailyWeatherStatRepository->findByDate($latitude, $longitude, $today);
        if ($todayStat !== null) {
            return $todayStat;
        }

        return $this->dailyWeatherStatRepository->findLatest($latitude, $longitude);
    }
}

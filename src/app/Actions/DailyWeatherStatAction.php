<?php

namespace App\Actions;

use App\Models\DailyWeatherStat;
use App\Repositories\CityRepository;
use App\Repositories\DailyWeatherStatRepository;
use App\Repositories\WeatherReportRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyWeatherStatAction
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly WeatherReportRepository $weatherReportRepository,
        private readonly DailyWeatherStatRepository $dailyWeatherStatRepository
    ) {
    }

    public function aggregateAndStore(int $cityId, ?CarbonInterface $date = null): ?DailyWeatherStat
    {
        $city = $this->cityRepository->findById($cityId);
        if ($city === null) {
            throw new \InvalidArgumentException("city_id={$cityId} が cities に存在しません。");
        }

        $targetDate = $date ? CarbonImmutable::instance($date) : CarbonImmutable::today('Asia/Tokyo');
        $aggregate = $this->weatherReportRepository->getDailyAggregateByCityId($targetDate, $cityId);
        if ($aggregate === null || $aggregate->avg_temperature === null) {
            return null;
        }

        return $this->dailyWeatherStatRepository->upsertByDate(
            $cityId,
            $targetDate,
            [
                'city_id' => $cityId,
                'latitude' => (float) $city->latitude,
                'longitude' => (float) $city->longitude,
                'average_temperature' => (float) $aggregate->avg_temperature,
                'average_humidity' => (float) $aggregate->avg_humidity,
                'average_wind_speed' => (float) $aggregate->avg_wind_speed,
                'average_precipitation' => (float) $aggregate->avg_precipitation,
                'max_temperature' => (float) $aggregate->max_temperature,
                'min_temperature' => (float) $aggregate->min_temperature,
            ]
        );
    }

    public function getMonthlyTrend(int $cityId): Collection
    {
        $today = CarbonImmutable::today('Asia/Tokyo');
        $fromDate = $today->subDays(29);

        return $this->dailyWeatherStatRepository->findMonthlyTrend($cityId, $fromDate, $today);
    }

    public function getTodayExtremes(int $cityId): ?DailyWeatherStat
    {
        $today = CarbonImmutable::today('Asia/Tokyo');
        $todayStat = $this->dailyWeatherStatRepository->findByDate($cityId, $today);
        if ($todayStat !== null) {
            return $todayStat;
        }

        return $this->dailyWeatherStatRepository->findLatest($cityId);
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\CityAction;
use App\Models\DailyWeatherStat;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SeedDailyWeatherStatsCommand extends Command
{
    protected $signature = 'weather:seed-daily {--days=30} {--city-id=}';

    protected $description = 'ダッシュボード確認用に日次気象データを投入する';

    public function handle(CityAction $cityAction): int
    {
        $days = max(1, (int) $this->option('days'));
        $cityIdOption = $this->option('city-id');
        $start = CarbonImmutable::today('Asia/Tokyo')->subDays($days - 1);

        if ($cityIdOption !== null && $cityIdOption !== '') {
            $city = $cityAction->findById((int) $cityIdOption);
            if ($city === null) {
                $this->error('指定された city_id が cities に存在しません。');
                return self::FAILURE;
            }
            $this->seedCity((int) $city->id, (float) $city->latitude, (float) $city->longitude, $start, $days);
            $this->info("city_id={$city->id} に{$days}日分のテストデータを投入しました。");
            return self::SUCCESS;
        }

        $cities = $cityAction->getAll();
        if ($cities->isEmpty()) {
            $this->warn('cities テーブルが空のため、処理をスキップしました。');
            return self::FAILURE;
        }

        foreach ($cities as $city) {
            $this->seedCity((int) $city->id, (float) $city->latitude, (float) $city->longitude, $start, $days);
        }

        $this->info("全都市に{$days}日分のテストデータを投入しました。");
        return self::SUCCESS;
    }

    private function seedCity(int $cityId, float $latitude, float $longitude, CarbonImmutable $start, int $days): void
    {
        for ($i = 0; $i < $days; $i++) {
            $date = $start->addDays($i);
            $wave = sin(($i / max($days - 1, 1)) * 2 * M_PI);
            $avg = round(7 + (6 * $wave) + (mt_rand(-7, 7) / 10), 1);
            $max = round($avg + 4 + (mt_rand(0, 15) / 10), 1);
            $min = round($avg - 4 - (mt_rand(0, 15) / 10), 1);

            DailyWeatherStat::query()->updateOrCreate(
                [
                    'city_id' => $cityId,
                    'measured_date' => $date->toDateString(),
                ],
                [
                    'city_id' => $cityId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'average_temperature' => $avg,
                    'average_humidity' => round(60 + (mt_rand(-120, 120) / 10), 1),
                    'average_wind_speed' => round(2.8 + (mt_rand(-10, 10) / 10), 1),
                    'average_precipitation' => round(max(0, mt_rand(-5, 40) / 10), 1),
                    'max_temperature' => $max,
                    'min_temperature' => $min,
                ]
            );
        }
    }
}

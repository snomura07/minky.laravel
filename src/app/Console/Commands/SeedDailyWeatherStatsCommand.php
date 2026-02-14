<?php

namespace App\Console\Commands;

use App\Models\DailyWeatherStat;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SeedDailyWeatherStatsCommand extends Command
{
    protected $signature = 'weather:seed-daily {--days=30} {--lat=36.063} {--lon=136.218}';

    protected $description = 'ダッシュボード確認用に日次気象データを投入する';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $latitude = (float) $this->option('lat');
        $longitude = (float) $this->option('lon');
        $start = CarbonImmutable::today('Asia/Tokyo')->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $date = $start->addDays($i);
            $wave = sin(($i / max($days - 1, 1)) * 2 * M_PI);
            $avg = round(7 + (6 * $wave) + (mt_rand(-7, 7) / 10), 1);
            $max = round($avg + 4 + (mt_rand(0, 15) / 10), 1);
            $min = round($avg - 4 - (mt_rand(0, 15) / 10), 1);

            DailyWeatherStat::query()->updateOrCreate(
                [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'measured_date' => $date->toDateString(),
                ],
                [
                    'average_temperature' => $avg,
                    'average_humidity' => round(60 + (mt_rand(-120, 120) / 10), 1),
                    'average_wind_speed' => round(2.8 + (mt_rand(-10, 10) / 10), 1),
                    'average_precipitation' => round(max(0, mt_rand(-5, 40) / 10), 1),
                    'max_temperature' => $max,
                    'min_temperature' => $min,
                ]
            );
        }

        $this->info("{$days}日分のテストデータを投入しました。");
        return self::SUCCESS;
    }
}

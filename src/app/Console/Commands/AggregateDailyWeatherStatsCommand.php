<?php

namespace App\Console\Commands;

use App\Actions\CityAction;
use App\Actions\DailyWeatherStatAction;
use App\Actions\DiscodeAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AggregateDailyWeatherStatsCommand extends Command
{
    protected $signature = 'weather:aggregate-daily {--city-id=} {--date=}';

    protected $description = 'weather_reports から日次集計を作成し daily_weather_stats に保存する';

    public function handle(
        CityAction $cityAction,
        DailyWeatherStatAction $dailyWeatherStatAction,
        DiscodeAction $discodeAction
    ): int
    {
        $cityIdOption = $this->option('city-id');
        $dateOpt = $this->option('date');
        $date = $dateOpt ? CarbonImmutable::parse($dateOpt, 'Asia/Tokyo') : null;

        if ($cityIdOption !== null && $cityIdOption !== '') {
            $city = $cityAction->findById((int) $cityIdOption);
            if ($city === null) {
                $this->error('指定された city_id が cities に存在しません。');
                return self::FAILURE;
            }

            return $this->aggregateForCity(
                (int) $city->id,
                (string) $city->city_name,
                $date,
                $dailyWeatherStatAction,
                $discodeAction
            );
        }

        $cities = $cityAction->getAll();
        if ($cities->isEmpty()) {
            $this->warn('cities テーブルが空のため、処理をスキップしました。');
            return self::FAILURE;
        }

        $allSucceeded = true;
        foreach ($cities as $city) {
            $result = $this->aggregateForCity(
                (int) $city->id,
                (string) $city->city_name,
                $date,
                $dailyWeatherStatAction,
                $discodeAction
            );
            if ($result !== self::SUCCESS) {
                $allSucceeded = false;
            }
        }

        return $allSucceeded ? self::SUCCESS : self::FAILURE;
    }

    private function aggregateForCity(
        int $cityId,
        string $cityName,
        ?CarbonImmutable $date,
        DailyWeatherStatAction $dailyWeatherStatAction,
        DiscodeAction $discodeAction
    ): int {
        $stat = $dailyWeatherStatAction->aggregateAndStore($cityId, $date);
        if ($stat === null) {
            $this->warn(sprintf('[%s] 集計対象データが存在しません。', $cityName));
            return self::FAILURE;
        }

        $this->info(sprintf(
            '[%s] 保存完了: %s max=%.1f min=%.1f avg=%.1f',
            $cityName,
            $stat->measured_date,
            $stat->max_temperature,
            $stat->min_temperature,
            $stat->average_temperature
        ));

        $message = sprintf(
            "Daily Weather Stats for %s on %s:\nAvg Temperature: %.1f °C\nAvg Humidity: %.1f %%\nAvg Wind Speed: %.1f m/s\nAvg Precipitation: %.1f mm\nMax Temperature: %.1f °C\nMin Temperature: %.1f °C",
            $cityName,
            $stat->measured_date,
            $stat->average_temperature,
            $stat->average_humidity,
            $stat->average_wind_speed,
            $stat->average_precipitation,
            $stat->max_temperature,
            $stat->min_temperature
        );
        $notified = $discodeAction->sendMessage($message);
        if ($notified) {
            $this->info(sprintf('Discord通知: %s を送信しました。', $cityName));
        } else {
            $this->warn(sprintf('Discord通知: %s は送信スキップまたは失敗（設定未投入の可能性あり）。', $cityName));
        }

        return self::SUCCESS;
    }
}

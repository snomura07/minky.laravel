<?php

namespace App\Console\Commands;

use App\Actions\DailyWeatherStatAction;
use App\Actions\DiscodeAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AggregateDailyWeatherStatsCommand extends Command
{
    protected $signature = 'weather:aggregate-daily {--lat=36.063} {--lon=136.218} {--date=}';

    protected $description = 'weather_reports から日次集計を作成し daily_weather_stats に保存する';

    public function handle(
        DailyWeatherStatAction $dailyWeatherStatAction,
        DiscodeAction $discodeAction
    ): int
    {
        $latitude = (float) $this->option('lat');
        $longitude = (float) $this->option('lon');
        $dateOpt = $this->option('date');
        $date = $dateOpt ? CarbonImmutable::parse($dateOpt, 'Asia/Tokyo') : null;

        $stat = $dailyWeatherStatAction->aggregateAndStore($latitude, $longitude, $date);
        if ($stat === null) {
            $this->warn('集計対象データが存在しません。');
            return self::FAILURE;
        }

        $this->info(sprintf(
            '保存完了: %s max=%.1f min=%.1f avg=%.1f',
            $stat->measured_date,
            $stat->max_temperature,
            $stat->min_temperature,
            $stat->average_temperature
        ));

        $message = sprintf(
            "Daily Weather Stats for (%.3f, %.3f) on %s:\nAvg Temperature: %.1f °C\nAvg Humidity: %.1f %%\nAvg Wind Speed: %.1f m/s\nAvg Precipitation: %.1f mm\nMax Temperature: %.1f °C\nMin Temperature: %.1f °C",
            $latitude,
            $longitude,
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
            $this->info('Discord通知: 送信しました。');
        } else {
            $this->warn('Discord通知: 送信スキップまたは失敗（設定未投入の可能性あり）。');
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\DiscodeAction;
use App\Actions\WeatherReportAction;
use Illuminate\Console\Command;

class FetchCurrentWeatherCommand extends Command
{
    protected $signature = 'weather:fetch-current {--lat=36.063} {--lon=136.218}';

    protected $description = 'Open-Meteo から現在の気象データを取得して保存する';

    public function handle(
        WeatherReportAction $weatherReportAction,
        DiscodeAction $discodeAction
    ): int
    {
        $latitude = (float) $this->option('lat');
        $longitude = (float) $this->option('lon');

        $report = $weatherReportAction->fetchAndStore($latitude, $longitude);

        $this->info(sprintf(
            '保存完了: %s temp=%.1f humidity=%.1f wind=%.1f precipitation=%.1f',
            $report->measured_time,
            $report->temperature,
            $report->humidity,
            $report->wind_speed,
            $report->precipitation
        ));

        $message = sprintf(
            "Weather Report for (%.3f, %.3f):\nTime: %s\nTemperature: %.1f °C\nHumidity: %.1f %%\nWind Speed: %.1f m/s\nPrecipitation: %.1f mm",
            $report->latitude,
            $report->longitude,
            $report->measured_time,
            $report->temperature,
            $report->humidity,
            $report->wind_speed,
            $report->precipitation
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

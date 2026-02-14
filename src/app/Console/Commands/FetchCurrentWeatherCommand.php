<?php

namespace App\Console\Commands;

use App\Actions\CityAction;
use App\Actions\DiscodeAction;
use App\Actions\WeatherReportAction;
use Illuminate\Console\Command;

class FetchCurrentWeatherCommand extends Command
{
    protected $signature = 'weather:fetch-current';

    protected $description = 'Open-Meteo から現在の気象データを取得して保存する';

    public function handle(
        CityAction $cityAction,
        WeatherReportAction $weatherReportAction,
        DiscodeAction $discodeAction
    ): int
    {
        $cities = $cityAction->getAll();
        if ($cities->isEmpty()) {
            $this->warn('cities テーブルが空のため、処理をスキップしました。');
            return self::FAILURE;
        }

        foreach ($cities as $city) {
            $report = $weatherReportAction->fetchAndStore((int) $city->id);

            $this->info(sprintf(
                '保存完了: %s %s temp=%.1f humidity=%.1f wind=%.1f precipitation=%.1f',
                $city->city_name,
                $report->measured_time,
                $report->temperature,
                $report->humidity,
                $report->wind_speed,
                $report->precipitation
            ));

            $message = sprintf(
                "Weather Report for %s:\nTime: %s\nTemperature: %.1f °C\nHumidity: %.1f %%\nWind Speed: %.1f m/s\nPrecipitation: %.1f mm",
                $city->city_name,
                $report->measured_time,
                $report->temperature,
                $report->humidity,
                $report->wind_speed,
                $report->precipitation
            );
            $notified = $discodeAction->sendMessage($message);
            if ($notified) {
                $this->info(sprintf('Discord通知: %s を送信しました。', $city->city_name));
            } else {
                $this->warn(sprintf('Discord通知: %s は送信スキップまたは失敗（設定未投入の可能性あり）。', $city->city_name));
            }
        }

        return self::SUCCESS;
    }
}

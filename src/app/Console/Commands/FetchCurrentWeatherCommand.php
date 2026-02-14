<?php

namespace App\Console\Commands;

use App\Actions\WeatherReportAction;
use Illuminate\Console\Command;

class FetchCurrentWeatherCommand extends Command
{
    protected $signature = 'weather:fetch-current {--lat=36.063} {--lon=136.218}';

    protected $description = 'Open-Meteo から現在の気象データを取得して保存する';

    public function handle(WeatherReportAction $weatherReportAction): int
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

        return self::SUCCESS;
    }
}

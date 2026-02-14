<?php

namespace App\Console\Commands;

use App\Actions\DailyWeatherStatAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AggregateDailyWeatherStatsCommand extends Command
{
    protected $signature = 'weather:aggregate-daily {--lat=36.063} {--lon=136.218} {--date=}';

    protected $description = 'weather_reports から日次集計を作成し daily_weather_stats に保存する';

    public function handle(DailyWeatherStatAction $dailyWeatherStatAction): int
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

        return self::SUCCESS;
    }
}

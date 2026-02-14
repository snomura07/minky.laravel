<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWeatherStat extends Model
{
    protected $table = 'daily_weather_stats';

    protected $fillable = [
        'city_id',
        'latitude',
        'longitude',
        'measured_date',
        'average_temperature',
        'average_humidity',
        'average_wind_speed',
        'average_precipitation',
        'max_temperature',
        'min_temperature',
    ];

    protected $casts = [
        'city_id' => 'integer',
        'measured_date' => 'date',
        'average_temperature' => 'float',
        'average_humidity' => 'float',
        'average_wind_speed' => 'float',
        'average_precipitation' => 'float',
        'max_temperature' => 'float',
        'min_temperature' => 'float',
    ];
}

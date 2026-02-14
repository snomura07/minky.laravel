<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherReport extends Model
{
    protected $table = 'weather_reports';

    protected $fillable = [
        'latitude',
        'longitude',
        'measured_time',
        'temperature',
        'humidity',
        'wind_speed',
        'precipitation',
    ];

    protected $casts = [
        'measured_time' => 'datetime',
        'temperature' => 'float',
        'humidity' => 'float',
        'wind_speed' => 'float',
        'precipitation' => 'float',
    ];
}

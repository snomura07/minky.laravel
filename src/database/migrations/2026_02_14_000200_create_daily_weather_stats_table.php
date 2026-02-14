<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_weather_stats', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 8, 5)->comment('緯度');
            $table->decimal('longitude', 8, 5)->comment('経度');
            $table->date('measured_date')->index()->comment('観測日');
            $table->float('average_temperature')->comment('平均気温[℃]');
            $table->float('average_humidity')->comment('平均湿度[%]');
            $table->float('average_wind_speed')->comment('平均風速[m/s]');
            $table->float('average_precipitation')->comment('平均降水量[mm]');
            $table->float('max_temperature')->comment('最高気温[℃]');
            $table->float('min_temperature')->comment('最低気温[℃]');
            $table->timestamps();

            $table->unique(['latitude', 'longitude', 'measured_date'], 'daily_weather_stats_unique_location_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_weather_stats');
    }
};

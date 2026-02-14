<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_reports', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 8, 5)->comment('緯度');
            $table->decimal('longitude', 8, 5)->comment('経度');
            $table->dateTime('measured_time')->index()->comment('観測日時');
            $table->float('temperature')->comment('気温[℃]');
            $table->float('humidity')->comment('湿度[%]');
            $table->float('wind_speed')->comment('風速[m/s]');
            $table->float('precipitation')->comment('降水量[mm]');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_reports');
    }
};

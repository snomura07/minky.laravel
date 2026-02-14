<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weather_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->index()->after('id')->comment('都市ID');
        });

        Schema::table('daily_weather_stats', function (Blueprint $table) {
            $table->dropUnique('daily_weather_stats_unique_location_date');
            $table->unsignedBigInteger('city_id')->nullable()->index()->after('id')->comment('都市ID');
            $table->unique(['city_id', 'measured_date'], 'daily_weather_stats_unique_city_date');
        });
    }

    public function down(): void
    {
        Schema::table('daily_weather_stats', function (Blueprint $table) {
            $table->dropUnique('daily_weather_stats_unique_city_date');
            $table->dropColumn('city_id');
            $table->unique(['latitude', 'longitude', 'measured_date'], 'daily_weather_stats_unique_location_date');
        });

        Schema::table('weather_reports', function (Blueprint $table) {
            $table->dropColumn('city_id');
        });
    }
};

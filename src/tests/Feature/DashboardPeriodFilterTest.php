<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\WeatherReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPeriodFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_chart_data_for_single_day(): void
    {
        $this->seedCity();
        $this->seedWeather(2, '2026-02-14 00:30:00', 3.2);
        $this->seedWeather(2, '2026-02-14 12:00:00', 8.7);
        $this->seedWeather(2, '2026-02-15 01:00:00', 2.1);

        $response = $this->get('/dashboard?from=2026-02-14&to=2026-02-14');

        $response->assertOk();
        $response->assertViewHas('periodLabel', '2026-02-14');
        $response->assertViewHas('selectedFrom', '2026-02-14');
        $response->assertViewHas('selectedTo', '2026-02-14');

        $chartData = $response->viewData('chartData');
        $this->assertCount(2, $chartData);
        $this->assertSame('00:30', $chartData[0]['time']);
        $this->assertSame('12:00', $chartData[1]['time']);
    }

    public function test_it_filters_chart_data_for_date_range(): void
    {
        $this->seedCity();
        $this->seedWeather(2, '2026-02-14 00:30:00', 3.2);
        $this->seedWeather(2, '2026-02-15 12:00:00', 8.7);
        $this->seedWeather(2, '2026-02-16 01:00:00', 2.1);
        $this->seedWeather(2, '2026-02-17 05:00:00', 10.4);

        $response = $this->get('/dashboard?from=2026-02-14&to=2026-02-16');

        $response->assertOk();
        $response->assertViewHas('periodLabel', '2026-02-14 〜 2026-02-16');

        $chartData = $response->viewData('chartData');
        $this->assertCount(3, $chartData);
        $this->assertSame('02/14 00:30', $chartData[0]['time']);
        $this->assertSame('02/16 01:00', $chartData[2]['time']);

        $dayStats = $response->viewData('dayStats');
        $this->assertSame(8.7, $dayStats['max_temperature']);
        $this->assertSame(2.1, $dayStats['min_temperature']);
    }

    public function test_it_rejects_range_longer_than_183_days(): void
    {
        $response = $this->from('/dashboard')->get('/dashboard?from=2025-01-01&to=2025-07-04');

        $response->assertStatus(302);
        $response->assertSessionHasErrors('to');
    }

    public function test_it_accepts_manual_y_range(): void
    {
        $this->seedCity();
        $this->seedWeather(2, '2026-02-14 00:30:00', 3.2);

        $response = $this->get('/dashboard?from=2026-02-14&to=2026-02-14&y_min=0&y_max=20');

        $response->assertOk();
        $response->assertViewHas('selectedYMin', 0.0);
        $response->assertViewHas('selectedYMax', 20.0);
    }

    public function test_it_rejects_invalid_manual_y_range(): void
    {
        $response = $this->from('/dashboard')->get('/dashboard?from=2026-02-14&to=2026-02-14&y_min=10&y_max=10');

        $response->assertStatus(302);
        $response->assertSessionHasErrors('y_max');
    }

    private function seedCity(): void
    {
        City::query()->create([
            'id' => 2,
            'prefecture_name' => 'Fukui',
            'city_name' => 'Fukui',
            'latitude' => 36.06300,
            'longitude' => 136.21800,
        ]);
    }

    private function seedWeather(int $cityId, string $measuredAt, float $temperature): void
    {
        $time = CarbonImmutable::parse($measuredAt, 'Asia/Tokyo');
        WeatherReport::query()->create([
            'city_id' => $cityId,
            'latitude' => 36.06300,
            'longitude' => 136.21800,
            'measured_time' => $time,
            'temperature' => $temperature,
            'humidity' => 50.0,
            'wind_speed' => 2.0,
            'precipitation' => 0.0,
        ]);
    }
}

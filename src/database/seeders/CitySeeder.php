<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cities')->upsert(
            [
                [
                    'id' => 1,
                    'prefecture_name' => 'Tokyo',
                    'city_name' => 'Tokyo',
                    'latitude' => 35.69000,
                    'longitude' => 139.69200,
                ],
                [
                    'id' => 2,
                    'prefecture_name' => 'Fukui',
                    'city_name' => 'Fukui',
                    'latitude' => 36.06300,
                    'longitude' => 136.21800,
                ],
            ],
            ['id'],
            ['prefecture_name', 'city_name', 'latitude', 'longitude']
        );
    }
}

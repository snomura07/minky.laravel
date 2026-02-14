<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('prefecture_name')->comment('都道府県名');
            $table->string('city_name')->comment('市名');
            $table->decimal('latitude', 8, 5)->comment('緯度');
            $table->decimal('longitude', 8, 5)->comment('経度');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};

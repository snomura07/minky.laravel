<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Support\Collection;

class CityRepository
{
    public function getAll(): Collection
    {
        return City::query()->orderBy('id')->get();
    }

    public function findById(int $id): ?City
    {
        return City::query()->find($id);
    }
}

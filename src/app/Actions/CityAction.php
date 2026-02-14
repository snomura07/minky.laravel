<?php

namespace App\Actions;

use App\Models\City;
use App\Repositories\CityRepository;
use Illuminate\Support\Collection;

class CityAction
{
    public function __construct(
        private readonly CityRepository $cityRepository
    ) {
    }

    public function getAll(): Collection
    {
        return $this->cityRepository->getAll();
    }

    public function findById(int $id): ?City
    {
        return $this->cityRepository->findById($id);
    }
}

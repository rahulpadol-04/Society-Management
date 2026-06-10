<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    protected array $filterable = ['type', 'status', 'flat_id'];

    protected array $searchable = ['registration_number', 'make', 'model'];

    protected function model(): string
    {
        return Vehicle::class;
    }
}

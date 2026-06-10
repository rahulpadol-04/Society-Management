<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ParkingSlot;
use App\Repositories\Contracts\ParkingSlotRepositoryInterface;

class ParkingSlotRepository extends BaseRepository implements ParkingSlotRepositoryInterface
{
    protected array $filterable = ['status', 'type', 'flat_id'];

    protected array $searchable = ['code', 'location'];

    protected function model(): string
    {
        return ParkingSlot::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\FacilityBooking;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;

class FacilityBookingRepository extends BaseRepository implements FacilityBookingRepositoryInterface
{
    protected array $filterable = ['status', 'facility_id', 'user_id'];

    protected array $searchable = ['notes'];

    protected function model(): string
    {
        return FacilityBooking::class;
    }
}

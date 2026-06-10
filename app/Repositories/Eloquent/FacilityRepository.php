<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Facility;
use App\Repositories\Contracts\FacilityRepositoryInterface;

class FacilityRepository extends BaseRepository implements FacilityRepositoryInterface
{
    protected array $filterable = ['type', 'is_active'];

    protected array $searchable = ['name'];

    protected function model(): string
    {
        return Facility::class;
    }
}

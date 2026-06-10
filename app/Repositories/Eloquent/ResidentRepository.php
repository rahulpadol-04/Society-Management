<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Resident;
use App\Repositories\Contracts\ResidentRepositoryInterface;

class ResidentRepository extends BaseRepository implements ResidentRepositoryInterface
{
    protected array $filterable = ['type', 'status', 'flat_id'];

    protected array $searchable = ['name', 'email', 'phone'];

    protected function model(): string
    {
        return Resident::class;
    }
}

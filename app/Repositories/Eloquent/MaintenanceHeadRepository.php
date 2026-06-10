<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\MaintenanceHead;
use App\Repositories\Contracts\MaintenanceHeadRepositoryInterface;

class MaintenanceHeadRepository extends BaseRepository implements MaintenanceHeadRepositoryInterface
{
    protected array $filterable = ['type', 'frequency', 'is_active'];

    protected array $searchable = ['name', 'code'];

    protected function model(): string
    {
        return MaintenanceHead::class;
    }
}

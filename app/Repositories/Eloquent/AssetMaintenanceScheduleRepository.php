<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\AssetMaintenanceSchedule;
use App\Repositories\Contracts\AssetMaintenanceScheduleRepositoryInterface;

class AssetMaintenanceScheduleRepository extends BaseRepository implements AssetMaintenanceScheduleRepositoryInterface
{
    protected array $filterable = ['status', 'asset_id'];

    protected function model(): string
    {
        return AssetMaintenanceSchedule::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetMaintenanceSchedule;
use App\Models\User;

/**
 * Auto-discovered policy (AssetMaintenanceSchedule -> AssetMaintenanceSchedulePolicy).
 */
class AssetMaintenanceSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, AssetMaintenanceSchedule $schedule): bool
    {
        return $user->can('assets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('assets.schedule');
    }

    public function update(User $user, AssetMaintenanceSchedule $schedule): bool
    {
        return $user->can('assets.schedule');
    }

    public function delete(User $user, AssetMaintenanceSchedule $schedule): bool
    {
        return $user->can('assets.delete');
    }
}

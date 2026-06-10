<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceHead;
use App\Models\User;

/**
 * Auto-discovered policy (MaintenanceHead -> MaintenanceHeadPolicy).
 */
class MaintenanceHeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function view(User $user, MaintenanceHead $head): bool
    {
        return $user->can('maintenance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.create');
    }

    public function update(User $user, MaintenanceHead $head): bool
    {
        return $user->can('maintenance.update');
    }

    public function delete(User $user, MaintenanceHead $head): bool
    {
        return $user->can('maintenance.delete');
    }
}

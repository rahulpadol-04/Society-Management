<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

/**
 * Auto-discovered policy (WorkOrder -> WorkOrderPolicy).
 */
class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendors.view');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->can('vendors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vendors.create');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->can('vendors.update');
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->can('vendors.delete');
    }
}

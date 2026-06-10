<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffShift;
use App\Models\User;

/**
 * Auto-discovered policy (StaffShift -> StaffShiftPolicy).
 */
class StaffShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('society-staff.view');
    }

    public function view(User $user, StaffShift $staffShift): bool
    {
        return $user->can('society-staff.view');
    }

    public function create(User $user): bool
    {
        return $user->can('society-staff.create');
    }

    public function update(User $user, StaffShift $staffShift): bool
    {
        return $user->can('society-staff.update');
    }

    public function delete(User $user, StaffShift $staffShift): bool
    {
        return $user->can('society-staff.delete');
    }
}

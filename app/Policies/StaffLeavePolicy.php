<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffLeave;
use App\Models\User;

/**
 * Auto-discovered policy (StaffLeave -> StaffLeavePolicy).
 */
class StaffLeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('society-staff.view');
    }

    public function view(User $user, StaffLeave $staffLeave): bool
    {
        return $user->can('society-staff.view');
    }

    public function create(User $user): bool
    {
        return $user->can('society-staff.update');
    }

    public function update(User $user, StaffLeave $staffLeave): bool
    {
        return $user->can('society-staff.update');
    }

    public function delete(User $user, StaffLeave $staffLeave): bool
    {
        return $user->can('society-staff.delete');
    }
}

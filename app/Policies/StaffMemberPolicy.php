<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffMember;
use App\Models\User;

/**
 * Auto-discovered policy (StaffMember -> StaffMemberPolicy).
 * The Gate::before hook in AuthServiceProvider lets Super Admins bypass all checks.
 */
class StaffMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('society-staff.view');
    }

    public function view(User $user, StaffMember $staffMember): bool
    {
        return $user->can('society-staff.view');
    }

    public function create(User $user): bool
    {
        return $user->can('society-staff.create');
    }

    public function update(User $user, StaffMember $staffMember): bool
    {
        return $user->can('society-staff.update');
    }

    public function delete(User $user, StaffMember $staffMember): bool
    {
        return $user->can('society-staff.delete');
    }

    public function attendance(User $user): bool
    {
        return $user->can('society-staff.attendance');
    }

    public function payroll(User $user): bool
    {
        return $user->can('society-staff.payroll');
    }
}

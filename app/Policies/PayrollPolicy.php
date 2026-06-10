<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

/**
 * Auto-discovered policy (Payroll -> PayrollPolicy).
 */
class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('society-staff.payroll');
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->can('society-staff.payroll');
    }

    public function create(User $user): bool
    {
        return $user->can('society-staff.payroll');
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->can('society-staff.payroll');
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->can('society-staff.payroll');
    }
}

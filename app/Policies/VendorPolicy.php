<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

/**
 * Auto-discovered policy (Vendor -> VendorPolicy).
 * The Gate::before hook in AuthServiceProvider lets Super Admins bypass all checks.
 */
class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendors.view');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vendors.create');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.update');
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.delete');
    }

    public function pay(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.pay');
    }

    public function rate(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.rate');
    }
}

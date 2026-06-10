<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceBill;
use App\Models\User;

/**
 * Auto-discovered policy (MaintenanceBill -> MaintenanceBillPolicy).
 * Residents may view their own bills; all other actions require the
 * corresponding maintenance.* permission.
 */
class MaintenanceBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('maintenance.view');
    }

    public function view(User $user, MaintenanceBill $bill): bool
    {
        // Resident may view their own bill.
        if ($bill->user_id === $user->id) {
            return true;
        }

        // Or if the flat's owner is this user (loaded or queried).
        if ($bill->flat && $bill->flat->owner_id === $user->id) {
            return true;
        }

        return $user->can('maintenance.view');
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.create');
    }

    public function update(User $user, MaintenanceBill $bill): bool
    {
        return $user->can('maintenance.update');
    }

    public function delete(User $user, MaintenanceBill $bill): bool
    {
        return $user->can('maintenance.delete');
    }

    public function generate(User $user): bool
    {
        return $user->can('maintenance.generate');
    }

    public function collect(User $user, MaintenanceBill $bill): bool
    {
        return $user->can('maintenance.collect');
    }

    public function waive(User $user, MaintenanceBill $bill): bool
    {
        return $user->can('maintenance.waive');
    }

    public function export(User $user): bool
    {
        return $user->can('maintenance.export');
    }
}

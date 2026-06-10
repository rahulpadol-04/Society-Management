<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Resident;
use App\Models\User;

/**
 * Auto-discovered policy (Resident → ResidentPolicy). Gate::before in
 * AuthServiceProvider already lets Super Admins bypass all checks.
 */
class ResidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('residents.view');
    }

    public function view(User $user, Resident $resident): bool
    {
        // A resident may always view their own profile record.
        if ($resident->user_id && $resident->user_id === $user->id) {
            return true;
        }

        return $user->can('residents.view');
    }

    public function create(User $user): bool
    {
        return $user->can('residents.create');
    }

    public function update(User $user, Resident $resident): bool
    {
        return $user->can('residents.update');
    }

    public function delete(User $user, Resident $resident): bool
    {
        return $user->can('residents.delete');
    }

    public function approve(User $user): bool
    {
        return $user->can('residents.approve');
    }

    public function export(User $user): bool
    {
        return $user->can('residents.export');
    }
}

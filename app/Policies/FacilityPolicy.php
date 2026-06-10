<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

/**
 * Auto-discovered policy (Facility -> FacilityPolicy).
 * The Gate::before hook in AuthServiceProvider already lets Super Admins bypass.
 */
class FacilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('facilities.view');
    }

    public function view(User $user, Facility $facility): bool
    {
        return $user->can('facilities.view');
    }

    public function create(User $user): bool
    {
        return $user->can('facilities.create');
    }

    public function update(User $user, Facility $facility): bool
    {
        return $user->can('facilities.update');
    }

    public function delete(User $user, Facility $facility): bool
    {
        return $user->can('facilities.delete');
    }

    public function book(User $user, Facility $facility): bool
    {
        return $user->can('facilities.book');
    }
}

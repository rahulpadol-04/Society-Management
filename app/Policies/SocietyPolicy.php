<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Society;
use App\Models\User;

/**
 * Governs platform-level Society management. Super Admins bypass via Gate::before.
 */
class SocietyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('societies.view');
    }

    public function view(User $user, Society $society): bool
    {
        return $user->can('societies.view');
    }

    public function create(User $user): bool
    {
        return $user->can('societies.create');
    }

    public function update(User $user, Society $society): bool
    {
        return $user->can('societies.update');
    }

    public function delete(User $user, Society $society): bool
    {
        return $user->can('societies.delete');
    }

    public function suspend(User $user, Society $society): bool
    {
        return $user->can('societies.suspend');
    }

    public function impersonate(User $user, Society $society): bool
    {
        return $user->can('societies.impersonate');
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorPass;

/**
 * Auto-discovered policy (VisitorPass -> VisitorPassPolicy).
 * Gate::before in AuthServiceProvider already lets Super Admins bypass all checks.
 */
class VisitorPassPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visitors.view');
    }

    public function view(User $user, VisitorPass $pass): bool
    {
        // Residents may always view their own pass.
        if ($pass->host_id === $user->id) {
            return true;
        }

        return $user->can('visitors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('visitors.create');
    }

    public function update(User $user, VisitorPass $pass): bool
    {
        // Host can also update their own pass.
        if ($pass->host_id === $user->id) {
            return true;
        }

        return $user->can('visitors.approve');
    }

    public function approve(User $user, VisitorPass $pass): bool
    {
        return $user->can('visitors.approve');
    }

    public function reject(User $user, VisitorPass $pass): bool
    {
        return $user->can('visitors.approve');
    }

    public function delete(User $user, VisitorPass $pass): bool
    {
        return $user->can('visitors.approve');
    }

    public function export(User $user): bool
    {
        return $user->can('visitors.export');
    }
}

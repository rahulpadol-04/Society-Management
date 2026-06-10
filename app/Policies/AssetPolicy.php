<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

/**
 * Auto-discovered policy (Asset -> AssetPolicy).
 * The Gate::before hook in AuthServiceProvider lets Super Admins bypass all checks.
 */
class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->can('assets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('assets.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->can('assets.update');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->can('assets.delete');
    }

    public function schedule(User $user, Asset $asset): bool
    {
        return $user->can('assets.schedule');
    }
}

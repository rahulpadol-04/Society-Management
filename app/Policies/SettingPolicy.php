<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

/**
 * Governs the society "master" configuration area. Super Admins bypass via the
 * Gate::before hook in AuthServiceProvider.
 */
class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.view');
    }

    public function update(User $user): bool
    {
        return $user->can('settings.update');
    }

    public function roles(User $user): bool
    {
        return $user->can('settings.roles');
    }

    public function permissions(User $user): bool
    {
        return $user->can('settings.permissions');
    }
}

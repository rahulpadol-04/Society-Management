<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tower;
use App\Models\User;

/**
 * Towers, floors and flats are all governed by the "structure" module
 * permissions. Super Admins bypass via the Gate::before hook.
 */
class TowerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('structure.view');
    }

    public function view(User $user, Tower $tower): bool
    {
        return $user->can('structure.view');
    }

    public function create(User $user): bool
    {
        return $user->can('structure.create');
    }

    public function update(User $user, Tower $tower): bool
    {
        return $user->can('structure.update');
    }

    public function delete(User $user, Tower $tower): bool
    {
        return $user->can('structure.delete');
    }
}

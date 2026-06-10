<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Flat;
use App\Models\User;

class FlatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('structure.view');
    }

    public function view(User $user, Flat $flat): bool
    {
        // Residents may view their own flat.
        if ($flat->owner_id === $user->id) {
            return true;
        }

        return $user->can('structure.view');
    }

    public function create(User $user): bool
    {
        return $user->can('structure.create');
    }

    public function update(User $user, Flat $flat): bool
    {
        return $user->can('structure.update');
    }

    public function delete(User $user, Flat $flat): bool
    {
        return $user->can('structure.delete');
    }
}

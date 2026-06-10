<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetCategory;
use App\Models\User;

/**
 * Auto-discovered policy (AssetCategory -> AssetCategoryPolicy).
 */
class AssetCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assets.view');
    }

    public function view(User $user, AssetCategory $category): bool
    {
        return $user->can('assets.view');
    }

    public function create(User $user): bool
    {
        return $user->can('assets.create');
    }

    public function update(User $user, AssetCategory $category): bool
    {
        return $user->can('assets.update');
    }

    public function delete(User $user, AssetCategory $category): bool
    {
        return $user->can('assets.delete');
    }
}

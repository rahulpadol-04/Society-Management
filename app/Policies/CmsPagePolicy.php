<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CmsPage;
use App\Models\User;

/**
 * Governs platform-level CMS page management. Super Admins bypass via Gate::before.
 */
class CmsPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cms.view');
    }

    public function view(User $user, CmsPage $page): bool
    {
        return $user->can('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cms.create');
    }

    public function update(User $user, CmsPage $page): bool
    {
        return $user->can('cms.update');
    }

    public function delete(User $user, CmsPage $page): bool
    {
        return $user->can('cms.delete');
    }

    public function publish(User $user, CmsPage $page): bool
    {
        return $user->can('cms.publish');
    }
}

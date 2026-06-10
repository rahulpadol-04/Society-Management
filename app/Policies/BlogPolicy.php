<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

/**
 * Governs platform-level Blog post management. Super Admins bypass via Gate::before.
 */
class BlogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('blog.view');
    }

    public function view(User $user, Blog $blog): bool
    {
        return $user->can('blog.view');
    }

    public function create(User $user): bool
    {
        return $user->can('blog.create');
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->can('blog.update');
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $user->can('blog.delete');
    }

    public function publish(User $user, Blog $blog): bool
    {
        return $user->can('blog.publish');
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notice;
use App\Models\User;

/**
 * Auto-discovered policy (Notice -> NoticePolicy). Super Admins bypass via
 * Gate::before in AuthServiceProvider.
 */
class NoticePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('notices.view');
    }

    public function view(User $user, Notice $notice): bool
    {
        // Residents and tenants may see published notices.
        if ($notice->is_published) {
            return $user->can('notices.view');
        }

        // Drafts are only visible to those with full notices.view + admin-level.
        return $user->can('notices.view') && $user->can('notices.create');
    }

    public function create(User $user): bool
    {
        return $user->can('notices.create');
    }

    public function update(User $user, Notice $notice): bool
    {
        return $user->can('notices.update');
    }

    public function delete(User $user, Notice $notice): bool
    {
        return $user->can('notices.delete');
    }

    public function publish(User $user, Notice $notice): bool
    {
        return $user->can('notices.publish');
    }
}

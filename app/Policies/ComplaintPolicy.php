<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

/**
 * Auto-discovered policy (Complaint -> ComplaintPolicy). The Gate::before hook
 * in AuthServiceProvider already lets Super Admins bypass all checks.
 */
class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('complaints.view');
    }

    public function view(User $user, Complaint $complaint): bool
    {
        // Residents may always view their own complaint.
        if ($complaint->raised_by === $user->id) {
            return true;
        }

        return $user->can('complaints.view');
    }

    public function create(User $user): bool
    {
        return $user->can('complaints.create');
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $user->can('complaints.update') || $complaint->assigned_to === $user->id;
    }

    public function assign(User $user): bool
    {
        return $user->can('complaints.assign');
    }

    public function resolve(User $user, Complaint $complaint): bool
    {
        return $user->can('complaints.resolve') || $complaint->assigned_to === $user->id;
    }

    public function feedback(User $user, Complaint $complaint): bool
    {
        return $complaint->raised_by === $user->id;
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->can('complaints.update');
    }
}

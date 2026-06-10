<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisitorLog;

/**
 * Auto-discovered policy (VisitorLog -> VisitorLogPolicy).
 */
class VisitorLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visitors.view');
    }

    public function view(User $user, VisitorLog $log): bool
    {
        // Host of the related pass may view the log.
        if ($log->pass && $log->pass->host_id === $user->id) {
            return true;
        }

        return $user->can('visitors.view');
    }

    public function checkin(User $user): bool
    {
        return $user->can('visitors.checkin');
    }

    public function checkout(User $user, VisitorLog $log): bool
    {
        return $user->can('visitors.checkout');
    }

    public function export(User $user): bool
    {
        return $user->can('visitors.export');
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

/**
 * Auto-discovered policy (Poll -> PollPolicy). Super Admins bypass via
 * Gate::before in AuthServiceProvider.
 */
class PollPolicy
{
    public function vote(User $user, Poll $poll): bool
    {
        return $user->can('notices.vote');
    }

    public function manage(User $user): bool
    {
        return $user->can('notices.create');
    }

    public function close(User $user, Poll $poll): bool
    {
        return $user->can('notices.update');
    }
}

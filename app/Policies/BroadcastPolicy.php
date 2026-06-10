<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Broadcast;
use App\Models\User;

/**
 * Auto-discovered policy for Broadcast -> BroadcastPolicy.
 * Uses the three custom abilities declared in the communication module:
 *   communication.view, communication.broadcast, communication.send.
 */
class BroadcastPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.view');
    }

    public function view(User $user, Broadcast $broadcast): bool
    {
        return $user->can('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->can('communication.broadcast');
    }

    public function update(User $user, Broadcast $broadcast): bool
    {
        return $user->can('communication.broadcast');
    }

    public function delete(User $user, Broadcast $broadcast): bool
    {
        return $user->can('communication.broadcast');
    }

    public function send(User $user, Broadcast $broadcast): bool
    {
        return $user->can('communication.send');
    }
}

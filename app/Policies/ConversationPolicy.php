<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

/**
 * Auto-discovered policy for Conversation -> ConversationPolicy.
 * Participants may always view their own thread; communication.view unlocks
 * admin-level access across all conversations.
 */
class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.view') || true; // everyone can see their inbox
    }

    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->can('communication.view')) {
            return true;
        }

        return $conversation->participants()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('communication.send');
    }
}

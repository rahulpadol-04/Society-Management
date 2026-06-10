<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MessageTemplate;
use App\Models\User;

/**
 * Auto-discovered policy for MessageTemplate -> MessageTemplatePolicy.
 * Guarded by the communication.templates ability.
 */
class MessageTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.templates');
    }

    public function view(User $user, MessageTemplate $template): bool
    {
        return $user->can('communication.templates');
    }

    public function create(User $user): bool
    {
        return $user->can('communication.templates');
    }

    public function update(User $user, MessageTemplate $template): bool
    {
        return $user->can('communication.templates');
    }

    public function delete(User $user, MessageTemplate $template): bool
    {
        return $user->can('communication.templates');
    }
}

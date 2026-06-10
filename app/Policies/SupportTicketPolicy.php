<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

/**
 * Auto-discovered policy (SupportTicket -> SupportTicketPolicy).
 * The Gate::before hook in AuthServiceProvider lets Super Admins bypass all checks.
 */
class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('helpdesk.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        // Residents and tenants may always view their own ticket.
        if ($ticket->raised_by === $user->id) {
            return true;
        }

        return $user->can('helpdesk.view');
    }

    public function create(User $user): bool
    {
        return $user->can('helpdesk.create');
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return $user->can('helpdesk.update') || $ticket->assigned_to === $user->id;
    }

    public function assign(User $user): bool
    {
        return $user->can('helpdesk.assign');
    }

    public function escalate(User $user): bool
    {
        return $user->can('helpdesk.escalate');
    }

    public function close(User $user): bool
    {
        return $user->can('helpdesk.close');
    }

    public function delete(User $user, SupportTicket $ticket): bool
    {
        return $user->can('helpdesk.update');
    }
}

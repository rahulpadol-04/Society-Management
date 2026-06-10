<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/**
 * Governs platform-level Subscription management. Super Admins bypass via Gate::before.
 */
class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscriptions.view');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->can('subscriptions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('subscriptions.create');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->can('subscriptions.update');
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->can('subscriptions.cancel');
    }

    public function refund(User $user, Subscription $subscription): bool
    {
        return $user->can('subscriptions.refund');
    }
}

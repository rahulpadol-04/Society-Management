<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SubscriptionPlan;
use App\Models\User;

/**
 * Governs platform-level SubscriptionPlan management. Super Admins bypass via Gate::before.
 */
class SubscriptionPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('plans.view');
    }

    public function view(User $user, SubscriptionPlan $plan): bool
    {
        return $user->can('plans.view');
    }

    public function create(User $user): bool
    {
        return $user->can('plans.create');
    }

    public function update(User $user, SubscriptionPlan $plan): bool
    {
        return $user->can('plans.update');
    }

    public function delete(User $user, SubscriptionPlan $plan): bool
    {
        return $user->can('plans.delete');
    }
}

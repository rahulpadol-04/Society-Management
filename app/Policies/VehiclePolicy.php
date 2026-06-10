<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

/**
 * Auto-discovered policy (Vehicle → VehiclePolicy).
 */
class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vehicles.view');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        // An owner may view their own vehicle.
        if ($vehicle->resident && $vehicle->resident->user_id === $user->id) {
            return true;
        }

        return $user->can('vehicles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('vehicles.create');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        // Owner may update their own vehicle.
        if ($vehicle->resident && $vehicle->resident->user_id === $user->id) {
            return true;
        }

        return $user->can('vehicles.update');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Owner may delete their own vehicle.
        if ($vehicle->resident && $vehicle->resident->user_id === $user->id) {
            return true;
        }

        return $user->can('vehicles.delete');
    }
}

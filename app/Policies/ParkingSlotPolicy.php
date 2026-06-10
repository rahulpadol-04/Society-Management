<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ParkingSlot;
use App\Models\User;

class ParkingSlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('parking.view');
    }

    public function view(User $user, ParkingSlot $slot): bool
    {
        return $user->can('parking.view');
    }

    public function create(User $user): bool
    {
        return $user->can('parking.create');
    }

    public function update(User $user, ParkingSlot $slot): bool
    {
        return $user->can('parking.update');
    }

    public function assign(User $user, ParkingSlot $slot): bool
    {
        return $user->can('parking.assign');
    }

    public function delete(User $user, ParkingSlot $slot): bool
    {
        return $user->can('parking.delete');
    }
}

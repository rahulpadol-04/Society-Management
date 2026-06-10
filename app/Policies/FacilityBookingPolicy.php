<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FacilityBooking;
use App\Models\User;

/**
 * Auto-discovered policy (FacilityBooking -> FacilityBookingPolicy).
 */
class FacilityBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('facilities.view');
    }

    public function view(User $user, FacilityBooking $booking): bool
    {
        // Booker may always view their own booking.
        if ($booking->user_id === $user->id) {
            return true;
        }

        return $user->can('facilities.view');
    }

    public function approve(User $user, FacilityBooking $booking): bool
    {
        return $user->can('facilities.approve');
    }

    public function reject(User $user, FacilityBooking $booking): bool
    {
        return $user->can('facilities.approve');
    }

    public function cancel(User $user, FacilityBooking $booking): bool
    {
        // Booker may cancel their own booking, or those with cancel permission.
        if ($booking->user_id === $user->id) {
            return true;
        }

        return $user->can('facilities.cancel');
    }
}

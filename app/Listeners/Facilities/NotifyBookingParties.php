<?php

declare(strict_types=1);

namespace App\Listeners\Facilities;

use App\Events\Facilities\FacilityBooked;
use App\Events\Facilities\FacilityBookingApproved;
use App\Models\User;
use App\Notifications\Facilities\FacilityBookingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Queued listener (auto-discovered) that notifies relevant parties when a
 * facility booking is created or approved. Mirrors the complaint module pattern.
 */
class NotifyBookingParties implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(FacilityBooked|FacilityBookingApproved $event): void
    {
        $booking = $event->booking->loadMissing('facility', 'booker');

        [$recipients, $action] = match (true) {
            $event instanceof FacilityBooked          => [$this->societyManagers($booking->society_id), 'booked'],
            $event instanceof FacilityBookingApproved => [collect([$booking->booker]), 'approved'],
        };

        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new FacilityBookingNotification($booking, $action));
        }
    }

    protected function societyManagers(int $societyId): Collection
    {
        return User::withoutGlobalScopes()
            ->where('society_id', $societyId)
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['society-admin', 'sub-admin']))
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Facilities;

use App\Events\Facilities\FacilityBooked;
use App\Events\Facilities\FacilityBookingApproved;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Repositories\Contracts\FacilityBookingRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates the facility booking lifecycle: slot availability, capacity
 * enforcement, status transitions, and side-effect events.
 */
class FacilityService extends BaseService
{
    public function __construct(FacilityBookingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new facility booking after validating slot availability and capacity.
     */
    public function book(array $data): FacilityBooking
    {
        return DB::transaction(function () use ($data) {
            $facility = Facility::findOrFail($data['facility_id']);

            // Capacity guard
            if ($facility->capacity !== null && (int) ($data['guests'] ?? 0) > $facility->capacity) {
                throw ValidationException::withMessages([
                    'guests' => "Guest count exceeds facility capacity of {$facility->capacity}.",
                ]);
            }

            // Slot availability guard
            if (! $this->isSlotAvailable(
                $facility->id,
                $data['booking_date'],
                $data['start_time'],
                $data['end_time'],
            )) {
                throw ValidationException::withMessages([
                    'start_time' => 'The selected time slot is already booked.',
                ]);
            }

            $status = $facility->requires_approval ? 'pending' : 'approved';

            /** @var FacilityBooking $booking */
            $booking = $this->repository->create([
                ...$data,
                'amount' => $facility->charge,
                'status' => $status,
            ]);

            FacilityBooked::dispatch($booking->loadMissing('facility'));

            return $booking;
        });
    }

    /**
     * Approve a pending booking.
     */
    public function approve(FacilityBooking $booking, int $userId): FacilityBooking
    {
        return DB::transaction(function () use ($booking, $userId) {
            $booking->update([
                'status'      => 'approved',
                'approved_by' => $userId,
            ]);

            FacilityBookingApproved::dispatch($booking->refresh()->loadMissing('facility', 'booker'));

            return $booking->refresh();
        });
    }

    /**
     * Reject a booking with an optional reason stored in notes.
     */
    public function reject(FacilityBooking $booking, ?string $reason = null): FacilityBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => 'rejected',
                'notes'  => $reason ?? $booking->notes,
            ]);

            return $booking->refresh();
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancel(FacilityBooking $booking): FacilityBooking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            return $booking->refresh();
        });
    }

    /**
     * Check if a time slot is free for a given facility and date.
     * Overlapping means start < requested_end AND end > requested_start.
     */
    public function isSlotAvailable(
        int $facilityId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $exceptBookingId = null
    ): bool {
        $query = FacilityBooking::where('facility_id', $facilityId)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($exceptBookingId !== null) {
            $query->where('id', '!=', $exceptBookingId);
        }

        return ! $query->exists();
    }
}

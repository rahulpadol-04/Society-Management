<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facilities\StoreFacilityBookingRequest;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Services\Facilities\FacilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(protected FacilityService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FacilityBooking::class);

        $bookings = FacilityBooking::with(['facility', 'booker', 'flat'])
            ->when(
                ! $request->user()->can('facilities.view') || $request->boolean('mine'),
                fn ($q) => $q->where('user_id', $request->user()->id)
            )
            ->latest()
            ->limit(500)
            ->get();

        return view('facilities.bookings.index', ['bookings' => $bookings]);
    }

    public function store(StoreFacilityBookingRequest $request, Facility $facility): RedirectResponse
    {
        $this->authorize('book', $facility);

        $data = array_merge($request->validated(), [
            'facility_id' => $facility->id,
            'user_id'     => $request->user()->id,
        ]);

        $booking = $this->service->book($data);

        return redirect()->route('facilities.show', $facility)
            ->with('success', 'Booking submitted. Status: '.ucfirst($booking->status).'.');
    }

    public function approve(Request $request, FacilityBooking $booking): RedirectResponse
    {
        $this->authorize('approve', $booking);

        $this->service->approve($booking, $request->user()->id);

        return back()->with('success', 'Booking approved.');
    }

    public function reject(Request $request, FacilityBooking $booking): RedirectResponse
    {
        $this->authorize('reject', $booking);

        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:500']])['reason'] ?? null;

        $this->service->reject($booking, $reason);

        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(Request $request, FacilityBooking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $this->service->cancel($booking);

        return back()->with('success', 'Booking cancelled.');
    }
}

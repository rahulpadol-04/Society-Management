<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Facilities;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facilities\StoreFacilityBookingRequest;
use App\Http\Resources\FacilityBookingResource;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Services\Facilities\FacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Facility booking flow: request -> approve/cancel. The double-booking /
 * slot-clash check and the approval workflow are owned by FacilityService;
 * the controller just wires the authenticated user and facility into it.
 */
class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected FacilityService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FacilityBooking::class);

        $bookings = $this->service->paginate(
            $request->only(['status', 'facility_id', 'user_id', 'search', 'sort', 'dir', 'per_page']),
            ['facility', 'booker'],
        );

        return $this->paginated(
            $bookings->setCollection(
                $bookings->getCollection()->map(fn ($b) => (new FacilityBookingResource($b))->resolve())
            )
        );
    }

    public function store(StoreFacilityBookingRequest $request, Facility $facility): JsonResponse
    {
        $this->authorize('book', $facility);

        $data = array_merge($request->validated(), [
            'facility_id' => $facility->id,
            'user_id'     => $request->user()->id,
        ]);

        $booking = $this->service->book($data);

        return $this->created(new FacilityBookingResource($booking->load('facility')), 'Booking submitted.');
    }

    public function approve(Request $request, FacilityBooking $booking): JsonResponse
    {
        $this->authorize('approve', $booking);

        $booking = $this->service->approve($booking, $request->user()->id);

        return $this->ok(new FacilityBookingResource($booking->load('facility', 'booker')), 'Booking approved.');
    }

    public function cancel(Request $request, FacilityBooking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $booking = $this->service->cancel($booking);

        return $this->ok(new FacilityBookingResource($booking->load('facility', 'booker')), 'Booking cancelled.');
    }
}

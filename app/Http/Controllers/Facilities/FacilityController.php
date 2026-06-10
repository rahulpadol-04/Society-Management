<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facilities\StoreFacilityRequest;
use App\Http\Requests\Facilities\UpdateFacilityRequest;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Services\Facilities\FacilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function __construct(protected FacilityService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Facility::class);

        $facilities = Facility::with('bookings')->latest()->get();

        $todayBookings   = FacilityBooking::whereDate('booking_date', today())->count();
        $pendingBookings = FacilityBooking::where('status', 'pending')->count();
        $totalBookings   = FacilityBooking::count();

        $recentBookings = FacilityBooking::with(['facility', 'booker'])
            ->latest()
            ->limit(50)
            ->get();

        return view('facilities.index', [
            'facilities'      => $facilities,
            'todayBookings'   => $todayBookings,
            'pendingBookings' => $pendingBookings,
            'totalBookings'   => $totalBookings,
            'recentBookings'  => $recentBookings,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Facility::class);

        return view('facilities.create');
    }

    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facilities/'.current_society_id(), 'public');
        }

        $facility = Facility::create($data);

        return redirect()->route('facilities.show', $facility)
            ->with('success', "Facility \"{$facility->name}\" created.");
    }

    public function show(Facility $facility): View
    {
        $this->authorize('view', $facility);

        $facility->load(['bookings' => fn ($q) => $q->with(['booker', 'flat'])->latest()->limit(50)]);

        return view('facilities.show', ['facility' => $facility]);
    }

    public function edit(Facility $facility): View
    {
        $this->authorize('update', $facility);

        return view('facilities.edit', ['facility' => $facility]);
    }

    public function update(UpdateFacilityRequest $request, Facility $facility): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('facilities/'.current_society_id(), 'public');
        }

        $facility->update($data);

        return redirect()->route('facilities.show', $facility)
            ->with('success', 'Facility updated.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $this->authorize('delete', $facility);

        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Facility deleted.');
    }
}

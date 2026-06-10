<?php

declare(strict_types=1);

namespace App\Http\Controllers\Structure;

use App\Http\Controllers\Controller;
use App\Http\Requests\Structure\StoreParkingSlotRequest;
use App\Models\Flat;
use App\Models\ParkingSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParkingSlotController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ParkingSlot::class);

        return view('structure.parking.index', [
            'slots' => ParkingSlot::with('flat')->orderBy('code')->get(),
            'flats' => Flat::orderBy('number')->get(['id', 'number']),
        ]);
    }

    public function store(StoreParkingSlotRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = ! empty($data['flat_id']) ? 'assigned' : $data['status'];

        ParkingSlot::create($data);

        return back()->with('success', 'Parking slot added.');
    }

    public function update(StoreParkingSlotRequest $request, ParkingSlot $parking_slot): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = ! empty($data['flat_id']) ? 'assigned' : ($data['status'] === 'assigned' ? 'available' : $data['status']);

        $parking_slot->update($data);

        return back()->with('success', 'Parking slot updated.');
    }

    public function destroy(ParkingSlot $parking_slot): RedirectResponse
    {
        $this->authorize('delete', $parking_slot);

        $parking_slot->delete();

        return back()->with('success', 'Parking slot removed.');
    }
}

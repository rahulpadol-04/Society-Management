<?php

declare(strict_types=1);

namespace App\Http\Controllers\Residents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residents\StoreVehicleRequest;
use App\Http\Requests\Residents\UpdateVehicleRequest;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(protected VehicleRepositoryInterface $repository) {}

    public function index(): View
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = $this->repository->query()
            ->with(['flat', 'resident', 'parkingSlot'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('vehicles.index', ['vehicles' => $vehicles]);
    }

    public function create(): View
    {
        $this->authorize('create', Vehicle::class);

        return view('vehicles.create', $this->formData());
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = $this->repository->create($request->validated());

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Vehicle {$vehicle->registration_number} registered.");
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorize('view', $vehicle);

        $vehicle->load(['flat', 'resident', 'parkingSlot']);

        return view('vehicles.show', ['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.edit', array_merge(['vehicle' => $vehicle], $this->formData()));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted.');
    }

    protected function formData(): array
    {
        return [
            'flats'     => Flat::orderBy('number')->get(['id', 'number']),
            'residents' => Resident::active()->orderBy('name')->get(['id', 'name', 'flat_id']),
        ];
    }
}

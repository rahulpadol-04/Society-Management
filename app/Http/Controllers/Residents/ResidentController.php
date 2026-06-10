<?php

declare(strict_types=1);

namespace App\Http\Controllers\Residents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residents\StoreResidentRequest;
use App\Http\Requests\Residents\UpdateResidentRequest;
use App\Models\EmergencyContact;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use App\Services\Residents\ResidentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ResidentController extends Controller
{
    public function __construct(protected ResidentService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Resident::class);

        $residents = $this->service->repository()->query()
            ->with(['flat', 'user'])
            ->whereIn('type', ['owner', 'tenant'])
            ->latest()
            ->limit(1000)
            ->get();

        return view('residents.index', ['residents' => $residents]);
    }

    public function create(): View
    {
        $this->authorize('create', Resident::class);

        return view('residents.create', $this->formData());
    }

    public function store(StoreResidentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('residents/'.current_society_id(), 'public');
        }

        $resident = $this->service->create($data);

        return redirect()->route('residents.show', $resident)
            ->with('success', "Resident {$resident->name} registered.");
    }

    public function show(Resident $resident): View
    {
        $this->authorize('view', $resident);

        $resident->load(['flat', 'user', 'familyMembers.flat', 'emergencyContacts', 'vehicles.flat']);

        return view('residents.show', [
            'resident' => $resident,
            'flats'    => Flat::orderBy('number')->get(['id', 'number']),
        ]);
    }

    public function edit(Resident $resident): View
    {
        $this->authorize('update', $resident);

        return view('residents.edit', array_merge(['resident' => $resident], $this->formData()));
    }

    public function update(UpdateResidentRequest $request, Resident $resident): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('residents/'.current_society_id(), 'public');
        }

        $this->service->update($resident->id, $data);

        return redirect()->route('residents.show', $resident)->with('success', 'Resident updated.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $this->authorize('delete', $resident);

        $resident->delete();

        return redirect()->route('residents.index')->with('success', 'Resident deleted.');
    }

    // -------------------------------------------------------------------------
    // Family member sub-resource
    // -------------------------------------------------------------------------

    public function storeFamilyMember(StoreResidentRequest $request, Resident $resident): RedirectResponse
    {
        $data = $request->validated();
        $this->service->attachFamilyMember($resident, $data);

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Family member added.');
    }

    public function destroyFamilyMember(Resident $resident, Resident $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $member->delete();

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Family member removed.');
    }

    // -------------------------------------------------------------------------
    // Emergency contact sub-resource
    // -------------------------------------------------------------------------

    public function storeEmergencyContact(Request $request, Resident $resident): RedirectResponse
    {
        $this->authorize('update', $resident);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'phone'      => ['required', 'string', 'max:30'],
            'relation'   => ['nullable', 'string', 'max:60'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $this->service->addEmergencyContact($resident, $data);

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Emergency contact added.');
    }

    public function destroyEmergencyContact(Resident $resident, EmergencyContact $contact): RedirectResponse
    {
        $this->authorize('update', $resident);

        $contact->delete();

        return redirect()->route('residents.show', $resident)
            ->with('success', 'Emergency contact removed.');
    }

    // -------------------------------------------------------------------------
    // CSV export
    // -------------------------------------------------------------------------

    public function export(): StreamedResponse
    {
        $this->authorize('export', Resident::class);

        $residents = $this->service->repository()->query()
            ->with(['flat'])
            ->whereIn('type', ['owner', 'tenant'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $filename = 'residents_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($residents): void {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Name', 'Type', 'Flat', 'Email', 'Phone', 'Status', 'Move-in Date']);

            foreach ($residents as $r) {
                fputcsv($fh, [
                    $r->name,
                    ucfirst($r->type),
                    $r->flat?->number ?? '',
                    $r->email ?? '',
                    $r->phone ?? '',
                    ucfirst($r->status),
                    $r->move_in_date?->format('Y-m-d') ?? '',
                ]);
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function formData(): array
    {
        return [
            'flats' => Flat::orderBy('number')->get(['id', 'number']),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }
}

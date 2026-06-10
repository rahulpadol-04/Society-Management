<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffMemberRequest;
use App\Http\Requests\Staff\UpdateStaffMemberRequest;
use App\Models\StaffAttendance;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(protected StaffService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StaffMember::class);

        $staff = $this->service->repository()->query()
            ->latest()
            ->limit(1000)
            ->get();

        $counts = $this->service->statusCounts();

        $today = now()->toDateString();

        $presentToday = StaffAttendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'half_day'])
            ->count();

        $onLeave = StaffMember::where('status', 'on_leave')->count();

        $departments = StaffMember::selectRaw('department, COUNT(*) AS total')
            ->groupBy('department')
            ->pluck('total', 'department')
            ->all();

        return view('staff.index', compact('staff', 'counts', 'presentToday', 'onLeave', 'departments'));
    }

    public function create(): View
    {
        $this->authorize('create', StaffMember::class);

        return view('staff.create');
    }

    public function store(StoreStaffMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff/'.current_society_id(), 'public');
        } else {
            unset($data['photo']);
        }

        $staff = $this->service->create($data);

        return redirect()->route('society-staff.show', $staff)
            ->with('success', "Staff member {$staff->name} added.");
    }

    public function show(StaffMember $societyStaff): View
    {
        $this->authorize('view', $societyStaff);

        $societyStaff->load(['attendances' => fn ($q) => $q->latest('date')->limit(30), 'leaves', 'payrolls']);

        return view('staff.show', ['staff' => $societyStaff]);
    }

    public function edit(StaffMember $societyStaff): View
    {
        $this->authorize('update', $societyStaff);

        return view('staff.edit', ['staff' => $societyStaff]);
    }

    public function update(UpdateStaffMemberRequest $request, StaffMember $societyStaff): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff/'.current_society_id(), 'public');
        } else {
            unset($data['photo']);
        }

        $this->service->update($societyStaff->id, $data);

        return redirect()->route('society-staff.show', $societyStaff)
            ->with('success', 'Staff member updated.');
    }

    public function destroy(StaffMember $societyStaff): RedirectResponse
    {
        $this->authorize('delete', $societyStaff);

        $societyStaff->delete();

        return redirect()->route('society-staff.index')
            ->with('success', 'Staff member removed.');
    }
}

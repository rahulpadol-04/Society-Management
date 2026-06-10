<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffLeaveRequest;
use App\Models\StaffLeave;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(protected StaffService $service) {}

    public function index(): View
    {
        $this->authorize('viewAny', StaffLeave::class);

        $leaves = StaffLeave::with('staffMember')
            ->latest()
            ->limit(500)
            ->get();

        $staffList = StaffMember::active()->orderBy('name')->get();

        return view('staff.leaves.index', compact('leaves', 'staffList'));
    }

    public function store(StoreStaffLeaveRequest $request): RedirectResponse
    {
        $data  = $request->validated();
        $staff = StaffMember::findOrFail($data['staff_member_id']);

        if (empty($data['days'])) {
            $from = \Carbon\Carbon::parse($data['from_date']);
            $to   = \Carbon\Carbon::parse($data['to_date']);
            $data['days'] = (int) $from->diffInDays($to) + 1;
        }

        $this->service->applyLeave($staff, $data);

        return redirect()->route('staff.leaves.index')
            ->with('success', 'Leave request submitted.');
    }

    public function approve(StaffLeave $leave): RedirectResponse
    {
        $this->authorize('update', $leave);

        $this->service->approveLeave($leave, (int) auth()->id());

        return back()->with('success', 'Leave approved.');
    }

    public function reject(StaffLeave $leave): RedirectResponse
    {
        $this->authorize('update', $leave);

        $this->service->rejectLeave($leave, (int) auth()->id());

        return back()->with('success', 'Leave rejected.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(protected StaffService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('attendance', StaffMember::class);

        $date  = $request->input('date', now()->toDateString());

        $staff = StaffMember::active()
            ->with(['attendances' => fn ($q) => $q->whereDate('date', $date)])
            ->orderBy('name')
            ->get();

        return view('staff.attendance.index', compact('staff', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('attendance', StaffMember::class);

        $data = $request->validate([
            'date'         => ['required', 'date'],
            'attendance'   => ['required', 'array'],
            'attendance.*.status' => ['required', 'in:present,absent,half_day,leave,holiday'],
        ]);

        $date = $data['date'];

        foreach ($data['attendance'] as $staffId => $row) {
            $staff = StaffMember::find((int) $staffId);

            if ($staff === null) {
                continue;
            }

            $this->service->markAttendance(
                $staff,
                $date,
                $row['status'],
                $row['check_in']  ?? null,
                $row['check_out'] ?? null,
                $row['notes']     ?? null,
            );
        }

        return redirect()->route('staff.attendance.index', ['date' => $date])
            ->with('success', "Attendance saved for {$date}.");
    }
}

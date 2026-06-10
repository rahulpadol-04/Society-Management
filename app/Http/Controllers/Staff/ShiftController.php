<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffShift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StaffShift::class);

        $shifts = StaffShift::orderBy('name')->get();

        return view('staff.shifts.index', compact('shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StaffShift::class);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:191'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        StaffShift::create($data);

        return back()->with('success', 'Shift created.');
    }

    public function update(Request $request, StaffShift $shift): RedirectResponse
    {
        $this->authorize('update', $shift);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:191'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $shift->update($data);

        return back()->with('success', 'Shift updated.');
    }

    public function destroy(StaffShift $shift): RedirectResponse
    {
        $this->authorize('delete', $shift);

        $shift->delete();

        return back()->with('success', 'Shift deleted.');
    }
}

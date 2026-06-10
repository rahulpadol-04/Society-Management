<?php

use App\Http\Controllers\Staff\AttendanceController;
use App\Http\Controllers\Staff\LeaveController;
use App\Http\Controllers\Staff\PayrollController;
use App\Http\Controllers\Staff\ShiftController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

/*
| Staff Management (web). Mounted inside the authenticated, tenant-scoped
| group defined in routes/web.php. Gated by the "staff" plan feature.
|
| NOTE: the specific staff/* sub-routes are declared BEFORE the staff resource
| so they are not shadowed by the staff/{societyStaff} wildcard.
*/
Route::middleware('feature:staff')->group(function () {

    // Staff directory (index - required for the sidebar).
    Route::get('staff', [StaffController::class, 'index'])->name('society-staff.index');

    // Attendance
    Route::get('staff/attendance', [AttendanceController::class, 'index'])->name('staff.attendance.index');
    Route::post('staff/attendance', [AttendanceController::class, 'store'])->name('staff.attendance.store');

    // Leaves
    Route::get('staff/leaves', [LeaveController::class, 'index'])->name('staff.leaves.index');
    Route::post('staff/leaves', [LeaveController::class, 'store'])->name('staff.leaves.store');
    Route::post('staff/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('staff.leaves.approve');
    Route::post('staff/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('staff.leaves.reject');

    // Payroll
    Route::get('staff/payroll', [PayrollController::class, 'index'])->name('staff.payroll.index');
    Route::post('staff/payroll/generate', [PayrollController::class, 'generate'])->name('staff.payroll.generate');
    Route::post('staff/payroll/{payroll}/pay', [PayrollController::class, 'markPaid'])->name('staff.payroll.pay');

    // Shifts
    Route::get('staff/shifts', [ShiftController::class, 'index'])->name('staff.shifts.index');
    Route::post('staff/shifts', [ShiftController::class, 'store'])->name('staff.shifts.store');
    Route::put('staff/shifts/{shift}', [ShiftController::class, 'update'])->name('staff.shifts.update');
    Route::delete('staff/shifts/{shift}', [ShiftController::class, 'destroy'])->name('staff.shifts.destroy');

    // Staff resource (create/store/show/edit/update/destroy) — declared last so
    // the {societyStaff} wildcard does not catch the sub-routes above.
    Route::resource('staff', StaffController::class)
        ->except(['index'])
        ->parameters(['staff' => 'societyStaff'])
        ->names([
            'create'  => 'society-staff.create',
            'store'   => 'society-staff.store',
            'show'    => 'society-staff.show',
            'edit'    => 'society-staff.edit',
            'update'  => 'society-staff.update',
            'destroy' => 'society-staff.destroy',
        ]);
});

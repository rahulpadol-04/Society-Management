<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Payroll;
use App\Models\Society;
use App\Models\StaffAttendance;
use App\Models\StaffLeave;
use App\Models\StaffMember;
use App\Models\StaffShift;
use App\Models\User;
use App\Services\Staff\StaffService;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        // ── 4 Shifts ─────────────────────────────────────────────────────────
        if (! StaffShift::where('society_id', $society->id)->exists()) {
            $shiftData = [
                ['name' => 'Morning',  'start_time' => '06:00', 'end_time' => '14:00', 'description' => 'Morning duty shift'],
                ['name' => 'Evening',  'start_time' => '14:00', 'end_time' => '22:00', 'description' => 'Evening duty shift'],
                ['name' => 'Night',    'start_time' => '22:00', 'end_time' => '06:00', 'description' => 'Night duty shift'],
                ['name' => 'General',  'start_time' => '09:00', 'end_time' => '18:00', 'description' => 'General office hours'],
            ];

            foreach ($shiftData as $shift) {
                StaffShift::create(array_merge($shift, ['society_id' => $society->id]));
            }
        }

        // ── 7 Staff Members ───────────────────────────────────────────────────
        if (StaffMember::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        $staffData = [
            ['name' => 'Ramesh Kumar',    'employee_code' => 'EMP-001', 'designation' => 'Security Guard',      'department' => 'security',     'phone' => '9800001001', 'salary' => 15000, 'shift' => 'morning', 'status' => 'active'],
            ['name' => 'Suresh Yadav',    'employee_code' => 'EMP-002', 'designation' => 'Security Guard',      'department' => 'security',     'phone' => '9800001002', 'salary' => 15000, 'shift' => 'night',   'status' => 'active'],
            ['name' => 'Meena Devi',      'employee_code' => 'EMP-003', 'designation' => 'Housekeeping Staff',  'department' => 'housekeeping', 'phone' => '9800001003', 'salary' => 12000, 'shift' => 'morning', 'status' => 'active'],
            ['name' => 'Raju Mishra',     'employee_code' => 'EMP-004', 'designation' => 'Plumber',             'department' => 'plumbing',     'phone' => '9800001004', 'salary' => 18000, 'shift' => 'general', 'status' => 'active'],
            ['name' => 'Deepak Singh',    'employee_code' => 'EMP-005', 'designation' => 'Electrician',         'department' => 'electrical',   'phone' => '9800001005', 'salary' => 20000, 'shift' => 'general', 'status' => 'active'],
            ['name' => 'Priya Sharma',    'employee_code' => 'EMP-006', 'designation' => 'Admin Executive',     'department' => 'admin',        'phone' => '9800001006', 'salary' => 25000, 'shift' => 'general', 'status' => 'active'],
            ['name' => 'Mohan Lal',       'employee_code' => 'EMP-007', 'designation' => 'Gardener',            'department' => 'gardening',    'phone' => '9800001007', 'salary' => 12000, 'shift' => 'morning', 'status' => 'on_leave'],
        ];

        $staffMembers = [];
        foreach ($staffData as $data) {
            $staffMembers[] = StaffMember::create(array_merge($data, [
                'society_id'   => $society->id,
                'joining_date' => now()->subMonths(rand(6, 24))->format('Y-m-d'),
            ]));
        }

        // ── Attendance for last 5 days ────────────────────────────────────────
        $adminUser = User::withoutGlobalScopes()->where('email', 'admin@greenvalley.test')->first()
            ?? User::withoutGlobalScopes()->where('society_id', $society->id)->first();

        $attendanceStatuses = ['present', 'present', 'present', 'present', 'absent', 'half_day'];

        for ($day = 1; $day <= 5; $day++) {
            $date = now()->subDays($day)->format('Y-m-d');

            foreach ($staffMembers as $i => $staff) {
                // Skip staff on leave
                if ($staff->status === 'on_leave') {
                    $status = 'leave';
                } else {
                    // Vary the status: mostly present with occasional absent/half_day
                    $status = $attendanceStatuses[($i + $day) % count($attendanceStatuses)];
                }

                StaffAttendance::updateOrCreate(
                    ['staff_member_id' => $staff->id, 'date' => $date],
                    [
                        'society_id' => $society->id,
                        'status'     => $status,
                        'check_in'   => $status === 'present' ? '08:00:00' : null,
                        'check_out'  => $status === 'present' ? '17:00:00' : null,
                        'marked_by'  => $adminUser?->id,
                    ]
                );
            }
        }

        // ── 2 Leave Requests (1 pending, 1 approved) ─────────────────────────
        // Pending leave
        StaffLeave::create([
            'society_id'      => $society->id,
            'staff_member_id' => $staffMembers[2]->id,  // Meena Devi
            'type'            => 'sick',
            'from_date'       => now()->addDays(3)->format('Y-m-d'),
            'to_date'         => now()->addDays(5)->format('Y-m-d'),
            'days'            => 3,
            'reason'          => 'Fever and cold',
            'status'          => 'pending',
        ]);

        // Approved leave (for the on_leave staff Mohan Lal)
        StaffLeave::create([
            'society_id'      => $society->id,
            'staff_member_id' => $staffMembers[6]->id,  // Mohan Lal
            'type'            => 'casual',
            'from_date'       => now()->subDays(2)->format('Y-m-d'),
            'to_date'         => now()->addDays(3)->format('Y-m-d'),
            'days'            => 6,
            'reason'          => 'Personal work',
            'status'          => 'approved',
            'approved_by'     => $adminUser?->id,
        ]);

        // ── Generate Payroll for current month ────────────────────────────────
        $period  = now()->format('Y-m');
        $service = app(StaffService::class);

        if (! Payroll::where('society_id', $society->id)->where('period', $period)->exists()) {
            $service->generatePayroll($period);
        }

        tenancy()->forget();
    }
}

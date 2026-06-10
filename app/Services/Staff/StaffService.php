<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\Payroll;
use App\Models\StaffAttendance;
use App\Models\StaffLeave;
use App\Models\StaffMember;
use App\Repositories\Contracts\StaffMemberRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates business logic for staff management: attendance marking,
 * leave management, and payroll generation. All mutations use DB transactions.
 */
class StaffService extends BaseService
{
    protected StaffMemberRepositoryInterface $staffRepository;

    public function __construct(StaffMemberRepositoryInterface $repository)
    {
        $this->staffRepository = $repository;
        $this->repository      = $repository;
    }

    public function statusCounts(): array
    {
        return $this->staffRepository->statusCounts();
    }

    /**
     * Mark or update attendance for a staff member on a given date.
     */
    public function markAttendance(
        StaffMember $staff,
        string $date,
        string $status,
        ?string $checkIn = null,
        ?string $checkOut = null,
        ?string $notes = null,
    ): StaffAttendance {
        return DB::transaction(function () use ($staff, $date, $status, $checkIn, $checkOut, $notes) {
            return StaffAttendance::updateOrCreate(
                [
                    'staff_member_id' => $staff->id,
                    'date'            => $date,
                ],
                [
                    'society_id' => $staff->society_id,
                    'check_in'   => $checkIn,
                    'check_out'  => $checkOut,
                    'status'     => $status,
                    'notes'      => $notes,
                    'marked_by'  => auth()->id(),
                ]
            );
        });
    }

    /**
     * Apply for a leave on behalf of a staff member.
     */
    public function applyLeave(StaffMember $staff, array $data): StaffLeave
    {
        return DB::transaction(function () use ($staff, $data) {
            return StaffLeave::create(array_merge($data, [
                'society_id'      => $staff->society_id,
                'staff_member_id' => $staff->id,
                'status'          => 'pending',
            ]));
        });
    }

    /**
     * Approve a leave request.
     */
    public function approveLeave(StaffLeave $leave, int $userId): StaffLeave
    {
        return DB::transaction(function () use ($leave, $userId) {
            $leave->update([
                'status'      => 'approved',
                'approved_by' => $userId,
            ]);

            return $leave->refresh();
        });
    }

    /**
     * Reject a leave request.
     */
    public function rejectLeave(StaffLeave $leave, int $userId): StaffLeave
    {
        return DB::transaction(function () use ($leave, $userId) {
            $leave->update([
                'status'      => 'rejected',
                'approved_by' => $userId,
            ]);

            return $leave->refresh();
        });
    }

    /**
     * Generate (or update) payroll records for all active staff for the given period.
     * basic = staff.salary, counts present/absent rows from attendance for that period,
     * net = basic + allowances - deductions.
     */
    public function generatePayroll(string $period): array
    {
        return DB::transaction(function () use ($period) {
            [$year, $month] = explode('-', $period);

            $staffMembers = StaffMember::active()->get();
            $payrolls     = collect();

            foreach ($staffMembers as $staff) {
                $presentCount = StaffAttendance::where('staff_member_id', $staff->id)
                    ->whereYear('date', (int) $year)
                    ->whereMonth('date', (int) $month)
                    ->whereIn('status', ['present', 'half_day'])
                    ->count();

                $absentCount = StaffAttendance::where('staff_member_id', $staff->id)
                    ->whereYear('date', (int) $year)
                    ->whereMonth('date', (int) $month)
                    ->where('status', 'absent')
                    ->count();

                $basic       = $staff->salary;
                $allowances  = 0.0;
                $deductions  = 0.0;
                $net         = round($basic + $allowances - $deductions, 2);

                $payroll = Payroll::updateOrCreate(
                    [
                        'staff_member_id' => $staff->id,
                        'period'          => $period,
                    ],
                    [
                        'society_id'  => $staff->society_id,
                        'basic'       => $basic,
                        'allowances'  => $allowances,
                        'deductions'  => $deductions,
                        'net'         => $net,
                        'days_present' => $presentCount,
                        'days_absent'  => $absentCount,
                        'status'      => 'processed',
                    ]
                );

                $payrolls->push($payroll);
            }

            return ['count' => $payrolls->count(), 'payrolls' => $payrolls];
        });
    }
}

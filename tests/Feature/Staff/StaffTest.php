<?php

declare(strict_types=1);

namespace Tests\Feature\Staff;

use App\Models\StaffAttendance;
use App\Models\StaffMember;
use App\Services\Staff\StaffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_a_staff_member(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/staff', [
            'name'        => 'John Security',
            'department'  => 'security',
            'designation' => 'Guard',
            'salary'      => 15000,
            'shift'       => 'morning',
            'status'      => 'active',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('staff_members', [
            'name'       => 'John Security',
            'society_id' => $society->id,
            'department' => 'security',
            'salary'     => 15000,
        ]);
    }

    public function test_mark_attendance_creates_or_updates_record(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $staff = StaffMember::create([
            'society_id' => $society->id,
            'name'       => 'Jane Doe',
            'department' => 'housekeeping',
            'salary'     => 12000,
            'status'     => 'active',
        ]);

        $service = app(StaffService::class);
        $date    = now()->toDateString();

        $att = $service->markAttendance($staff, $date, 'present', '09:00', '18:00');

        $this->assertEquals('present', $att->status);
        $this->assertDatabaseHas('staff_attendances', [
            'staff_member_id' => $staff->id,
            'date'            => $date,
            'status'          => 'present',
        ]);

        // Update existing record
        $updated = $service->markAttendance($staff, $date, 'half_day');

        $this->assertEquals('half_day', $updated->status);
        $this->assertDatabaseCount('staff_attendances', 1);
    }

    public function test_generate_payroll_computes_net(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $staff = StaffMember::create([
            'society_id' => $society->id,
            'name'       => 'Test Staff',
            'department' => 'admin',
            'salary'     => 30000,
            'status'     => 'active',
        ]);

        $service = app(StaffService::class);
        $period  = now()->format('Y-m');

        // Seed some attendance data for this period
        $date = now()->format('Y-m') . '-01';
        $service->markAttendance($staff, $date, 'present');
        $service->markAttendance($staff, now()->format('Y-m') . '-02', 'present');
        $service->markAttendance($staff, now()->format('Y-m') . '-03', 'absent');

        $result = $service->generatePayroll($period);

        $this->assertEquals(1, $result['count']);

        $payroll = $result['payrolls']->first();

        // net = basic (30000) + allowances (0) - deductions (0) = 30000
        $this->assertEquals(30000.0, $payroll->basic);
        $this->assertEquals(0.0, $payroll->allowances);
        $this->assertEquals(0.0, $payroll->deductions);
        $this->assertEquals(30000.0, $payroll->net);
        $this->assertEquals(2, $payroll->days_present);
        $this->assertEquals(1, $payroll->days_absent);
        $this->assertEquals('processed', $payroll->status);
    }

    public function test_tenant_isolation_staff_not_visible_across_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');

        StaffMember::create([
            'society_id' => $alpha->id,
            'name'       => 'Alpha Guard',
            'department' => 'security',
            'salary'     => 15000,
            'status'     => 'active',
        ]);

        $alphaAdmin = $this->admin($alpha);
        $this->actingAs($alphaAdmin)->get('/staff')->assertOk()->assertSee('Alpha Guard');

        // Switch to Beta society — must flush session first
        $beta      = $this->makeSociety('Beta Society', 'beta@test.com');
        $betaAdmin = $this->admin($beta);

        $this->flushSession();

        $this->actingAs($betaAdmin)
            ->get('/staff')
            ->assertOk()
            ->assertDontSee('Alpha Guard');

        // Beta sees zero staff
        $this->assertEquals(0, StaffMember::count());
    }

    public function test_permission_denied_for_non_admin_attendance(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->get('/staff/attendance')
            ->assertForbidden();
    }
}

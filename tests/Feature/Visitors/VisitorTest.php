<?php

declare(strict_types=1);

namespace Tests\Feature\Visitors;

use App\Models\VisitorLog;
use App\Models\VisitorPass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    // ── Pass Creation ────────────────────────────────────────────────────────

    public function test_resident_can_create_visitor_pass_with_generated_code(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $response = $this->actingAs($resident)->post('/visitors', [
            'name' => 'John Doe',
            'type' => 'guest',
        ]);

        $pass = VisitorPass::first();

        $this->assertNotNull($pass);
        $this->assertStringStartsWith('VP-', $pass->code);
        $this->assertEquals('John Doe', $pass->name);
        $this->assertEquals($resident->id, $pass->host_id);
        $response->assertRedirect("/visitors/{$pass->id}");
    }

    public function test_staff_created_pass_is_auto_approved(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/visitors', [
            'name' => 'Auto Approved',
            'type' => 'delivery',
        ]);

        $pass = VisitorPass::first();

        $this->assertNotNull($pass);
        $this->assertEquals('approved', $pass->status);
        $this->assertNotNull($pass->approved_at);
        $this->assertEquals($admin->id, $pass->approved_by);
    }

    public function test_resident_created_pass_is_pending(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)->post('/visitors', [
            'name' => 'Pending Guest',
            'type' => 'guest',
        ]);

        $pass = VisitorPass::first();
        $this->assertEquals('pending', $pass->status);
    }

    // ── Approve ──────────────────────────────────────────────────────────────

    public function test_admin_can_approve_a_pending_pass(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        // Resident creates a pending pass
        $this->actingAs($resident)->post('/visitors', ['name' => 'Guest X', 'type' => 'guest']);
        $pass = VisitorPass::first();
        $this->assertEquals('pending', $pass->status);

        // Admin approves it
        $this->actingAs($admin)->post("/visitors/{$pass->id}/approve");
        $pass->refresh();

        $this->assertEquals('approved', $pass->status);
        $this->assertNotNull($pass->approved_at);
        $this->assertEquals($admin->id, $pass->approved_by);
    }

    public function test_resident_cannot_approve_a_pass(): void
    {
        $society   = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident  = $this->makeUser($society, 'resident');
        $resident2 = $this->makeUser($society, 'resident');

        $this->actingAs($resident)->post('/visitors', ['name' => 'Guest Y', 'type' => 'guest']);
        $pass = VisitorPass::first();

        $this->actingAs($resident2)
            ->post("/visitors/{$pass->id}/approve")
            ->assertForbidden();

        $pass->refresh();
        $this->assertEquals('pending', $pass->status);
    }

    // ── Check-in / Check-out ─────────────────────────────────────────────────

    public function test_checkin_creates_log_and_increments_pass_entries(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        // Create an approved pass
        $this->actingAs($admin)->post('/visitors', ['name' => 'Gate Guest', 'type' => 'guest']);
        $pass = VisitorPass::first();
        $this->assertEquals('approved', $pass->status);

        // Check in via gate console walk-in form
        $this->actingAs($admin)->post('/visitors/gate/checkin', [
            'name' => 'Gate Guest',
            'type' => 'guest',
            'gate' => 'Main Gate',
        ]);

        $log = VisitorLog::first();

        $this->assertNotNull($log);
        $this->assertEquals('Gate Guest', $log->name);
        $this->assertEquals('in', $log->status);
        $this->assertNotNull($log->checked_in_at);
    }

    public function test_checkin_by_code_links_pass_and_increments_entries(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/visitors', ['name' => 'Code Guest', 'type' => 'guest']);
        $pass = VisitorPass::first();

        $this->actingAs($admin)->post('/visitors/gate/checkin-code', [
            'code' => $pass->code,
            'gate' => 'Side Gate',
        ]);

        $log = VisitorLog::first();
        $this->assertNotNull($log);
        $this->assertEquals($pass->id, $log->visitor_pass_id);

        $pass->refresh();
        $this->assertEquals(1, $pass->entries_used);
        // Pass is maxed at max_entries=1 by default, so status should be 'used'
        $this->assertEquals('used', $pass->status);
    }

    public function test_checkout_sets_status_out_and_records_time(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/visitors/gate/checkin', [
            'name' => 'Checkout Visitor',
            'type' => 'guest',
            'gate' => 'Main Gate',
        ]);

        $log = VisitorLog::first();
        $this->assertEquals('in', $log->status);
        $this->assertNull($log->checked_out_at);

        $this->actingAs($admin)->post("/visitors/logs/{$log->id}/checkout");

        $log->refresh();
        $this->assertEquals('out', $log->status);
        $this->assertNotNull($log->checked_out_at);
    }

    // ── Tenant Isolation ─────────────────────────────────────────────────────

    public function test_visitor_passes_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/visitors', [
            'name' => 'Alpha Visitor',
            'type' => 'guest',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        $this->actingAs($this->admin($beta))
            ->get('/visitors')
            ->assertOk()
            ->assertDontSee('Alpha Visitor');

        $this->assertEquals(1, VisitorPass::withoutGlobalScopes()->count());
    }

    // ── Permission Denied ────────────────────────────────────────────────────

    public function test_user_without_permission_cannot_access_visitors(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        // Family member has no visitors.view permission
        $family = $this->makeUser($society, 'family-member');

        $this->actingAs($family)
            ->get('/visitors')
            ->assertForbidden();
    }
}

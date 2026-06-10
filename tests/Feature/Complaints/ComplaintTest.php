<?php

declare(strict_types=1);

namespace Tests\Feature\Complaints;

use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_register_a_complaint_with_reference_and_timeline(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin = $this->admin($society);

        $response = $this->actingAs($admin)->post('/complaints', [
            'title'    => 'Water leakage in basement',
            'priority' => 'high',
        ]);

        $complaint = Complaint::first();

        $this->assertNotNull($complaint);
        $response->assertRedirect("/complaints/{$complaint->id}");
        $this->assertStringStartsWith('CMP-', $complaint->reference);
        $this->assertEquals('open', $complaint->status);
        $this->assertEquals($admin->id, $complaint->raised_by);
        $this->assertDatabaseHas('complaint_activities', [
            'complaint_id' => $complaint->id,
            'action'       => 'created',
        ]);
    }

    public function test_complaints_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/complaints', [
            'title' => 'Alpha only', 'priority' => 'low',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        // Beta admin must not see Alpha's complaint (tenant scope).
        $this->actingAs($this->admin($beta))
            ->get('/complaints')
            ->assertOk()
            ->assertDontSee('Alpha only');

        $this->assertEquals(1, Complaint::withoutGlobalScopes()->count());
    }

    public function test_status_change_records_activity_and_timestamps(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin = $this->admin($society);

        $this->actingAs($admin)->post('/complaints', ['title' => 'Fix gate', 'priority' => 'medium']);
        $complaint = Complaint::first();

        $this->actingAs($admin)->put("/complaints/{$complaint->id}", [
            'status' => 'resolved',
            'note'   => 'Gate repaired',
        ]);

        $complaint->refresh();
        $this->assertEquals('resolved', $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertDatabaseHas('complaint_activities', [
            'complaint_id' => $complaint->id,
            'action'       => 'status_changed',
            'to_status'    => 'resolved',
        ]);
    }
}

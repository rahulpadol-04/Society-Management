<?php

declare(strict_types=1);

namespace Tests\Feature\Helpdesk;

use App\Models\SupportTicket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    // -----------------------------------------------------------------------
    // Ticket creation
    // -----------------------------------------------------------------------

    public function test_admin_can_create_ticket_with_tkt_reference_and_activity(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/helpdesk', [
            'subject'  => 'Billing discrepancy for March',
            'category' => 'billing',
            'priority' => 'high',
        ]);

        $ticket = SupportTicket::first();

        $this->assertNotNull($ticket);
        $response->assertRedirect("/helpdesk/{$ticket->id}");
        $this->assertStringStartsWith('TKT-', $ticket->ticket_number);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($admin->id, $ticket->raised_by);
        $this->assertNotNull($ticket->sla_due_at);
        $this->assertDatabaseHas('ticket_activities', [
            'support_ticket_id' => $ticket->id,
            'action'            => 'created',
        ]);
    }

    // -----------------------------------------------------------------------
    // Tenant isolation
    // -----------------------------------------------------------------------

    public function test_tickets_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/helpdesk', [
            'subject'  => 'Alpha only ticket',
            'category' => 'general',
            'priority' => 'low',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        $this->flushSession();

        $this->actingAs($this->admin($beta))
            ->get('/helpdesk')
            ->assertOk()
            ->assertDontSee('Alpha only ticket');

        $this->assertEquals(1, SupportTicket::withoutGlobalScopes()->count());
    }

    // -----------------------------------------------------------------------
    // Reply
    // -----------------------------------------------------------------------

    public function test_admin_can_add_reply_to_ticket(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/helpdesk', [
            'subject'  => 'Gate access issue',
            'category' => 'security',
            'priority' => 'medium',
        ]);
        $ticket = SupportTicket::first();

        $this->actingAs($admin)->post("/helpdesk/{$ticket->id}/reply", [
            'message'     => 'We are looking into your issue.',
            'is_internal' => false,
        ]);

        $this->assertDatabaseHas('ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'message'           => 'We are looking into your issue.',
            'is_internal'       => false,
        ]);
    }

    // -----------------------------------------------------------------------
    // Assign
    // -----------------------------------------------------------------------

    public function test_admin_can_assign_ticket_and_status_moves_to_in_progress(): void
    {
        $society   = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin     = $this->admin($society);
        $subAdmin  = $this->makeUser($society, 'sub-admin');

        $this->actingAs($admin)->post('/helpdesk', [
            'subject'  => 'Pool maintenance',
            'category' => 'facility',
            'priority' => 'medium',
        ]);
        $ticket = SupportTicket::first();

        $this->actingAs($admin)->post("/helpdesk/{$ticket->id}/assign", [
            'assigned_to' => $subAdmin->id,
            'note'        => 'Please handle urgently',
        ]);

        $ticket->refresh();
        $this->assertEquals($subAdmin->id, $ticket->assigned_to);
        $this->assertEquals('in_progress', $ticket->status);
        $this->assertDatabaseHas('ticket_activities', [
            'support_ticket_id' => $ticket->id,
            'action'            => 'assigned',
        ]);
    }

    // -----------------------------------------------------------------------
    // Close
    // -----------------------------------------------------------------------

    public function test_admin_can_close_ticket_and_timestamps_are_set(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/helpdesk', [
            'subject'  => 'Temporary power outage',
            'category' => 'technical',
            'priority' => 'urgent',
        ]);
        $ticket = SupportTicket::first();

        $this->actingAs($admin)->post("/helpdesk/{$ticket->id}/close", [
            'note' => 'Power restored, issue closed.',
        ]);

        $ticket->refresh();
        $this->assertEquals('closed', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
        $this->assertDatabaseHas('ticket_activities', [
            'support_ticket_id' => $ticket->id,
            'action'            => 'status_changed',
            'to_status'         => 'closed',
        ]);
    }

    // -----------------------------------------------------------------------
    // Resident can create and see own ticket only, cannot assign
    // -----------------------------------------------------------------------

    public function test_resident_can_create_ticket_and_sees_own_only(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        // Admin creates a ticket
        $this->actingAs($admin)->post('/helpdesk', [
            'subject'  => 'Admin private ticket',
            'category' => 'general',
            'priority' => 'low',
        ]);

        // Resident creates their own ticket
        $this->actingAs($resident)->post('/helpdesk', [
            'subject'  => 'Resident own ticket',
            'category' => 'account',
            'priority' => 'low',
        ]);

        $residentTicket = SupportTicket::where('raised_by', $resident->id)->first();
        $this->assertNotNull($residentTicket);

        // Resident can view their own ticket
        $this->actingAs($resident)
            ->get("/helpdesk/{$residentTicket->id}")
            ->assertOk();

        // Resident cannot assign
        $subAdmin = $this->makeUser($society, 'sub-admin');
        $this->actingAs($resident)
            ->post("/helpdesk/{$residentTicket->id}/assign", ['assigned_to' => $subAdmin->id])
            ->assertForbidden();
    }
}

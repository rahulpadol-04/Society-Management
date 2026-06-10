<?php

declare(strict_types=1);

namespace Tests\Feature\Communication;

use App\Jobs\Communication\DeliverBroadcast;
use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Services\Communication\CommunicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    // -------------------------------------------------------------------------
    // Broadcast: create + send resolves recipients from audience + marks sent
    // -------------------------------------------------------------------------

    public function test_admin_can_create_broadcast(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/communication/broadcasts', [
            'title'    => 'Test Broadcast',
            'message'  => 'Hello residents!',
            'channels' => ['email'],
            'audience' => 'all',
        ]);

        $broadcast = Broadcast::first();
        $this->assertNotNull($broadcast);
        $response->assertRedirect("/communication/broadcasts/{$broadcast->id}");
        $this->assertEquals('draft', $broadcast->status);
        $this->assertEquals('Test Broadcast', $broadcast->title);
    }

    public function test_send_broadcast_resolves_recipients_and_queues_job(): void
    {
        Queue::fake();

        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        // Create a draft broadcast.
        $broadcast = Broadcast::create([
            'society_id' => $society->id,
            'title'      => 'Residents Blast',
            'message'    => 'Important update for residents.',
            'channels'   => ['email'],
            'audience'   => 'residents',
            'status'     => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post("/communication/broadcasts/{$broadcast->id}/send")
            ->assertRedirect("/communication/broadcasts/{$broadcast->id}");

        $broadcast->refresh();

        // Status should be 'queued' and recipients created.
        $this->assertEquals('queued', $broadcast->status);
        $this->assertGreaterThan(0, $broadcast->recipients_count);

        // BroadcastRecipient rows were created.
        $this->assertDatabaseHas('broadcast_recipients', [
            'broadcast_id' => $broadcast->id,
            'user_id'      => $resident->id,
            'channel'      => 'email',
            'status'       => 'pending',
        ]);

        // DeliverBroadcast job was dispatched.
        Queue::assertPushed(DeliverBroadcast::class, fn ($job) => $job->broadcastId === $broadcast->id);
    }

    public function test_deliver_broadcast_job_marks_recipients_sent(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        $broadcast = Broadcast::create([
            'society_id' => $society->id,
            'title'      => 'Job Test',
            'message'    => 'Testing the job.',
            'channels'   => ['email'],
            'audience'   => 'all',
            'status'     => 'draft',
            'created_by' => $admin->id,
        ]);

        BroadcastRecipient::create([
            'society_id'   => $society->id,
            'broadcast_id' => $broadcast->id,
            'user_id'      => $resident->id,
            'channel'      => 'email',
            'status'       => 'pending',
        ]);

        // Run the job synchronously.
        (new DeliverBroadcast($broadcast->id))->handle();

        $this->assertDatabaseHas('broadcast_recipients', [
            'broadcast_id' => $broadcast->id,
            'user_id'      => $resident->id,
            'status'       => 'sent',
        ]);

        $broadcast->refresh();
        $this->assertEquals('sent', $broadcast->status);
        $this->assertNotNull($broadcast->sent_at);
    }

    // -------------------------------------------------------------------------
    // Templates
    // -------------------------------------------------------------------------

    public function test_admin_can_create_a_template(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/communication/templates', [
            'name'    => 'Welcome Email',
            'channel' => 'email',
            'subject' => 'Welcome!',
            'body'    => 'Hello {{ name }}, welcome!',
        ])->assertRedirect(route('communication.templates.index'));

        $this->assertDatabaseHas('message_templates', [
            'society_id' => $society->id,
            'name'       => 'Welcome Email',
            'channel'    => 'email',
        ]);
    }

    // -------------------------------------------------------------------------
    // Internal messaging
    // -------------------------------------------------------------------------

    public function test_admin_can_start_a_conversation_and_post_messages(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        /** @var CommunicationService $service */
        $service = app(CommunicationService::class);

        $conversation = $service->startConversation(
            [$resident->id],
            'Parking query',
            'Are slots available this weekend?',
            $admin->id,
        );

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('Parking query', $conversation->subject);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id'         => $admin->id,
            'body'            => 'Are slots available this weekend?',
        ]);

        // Both admin and resident are participants.
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id'         => $admin->id,
        ]);
        $this->assertDatabaseHas('conversation_participants', [
            'conversation_id' => $conversation->id,
            'user_id'         => $resident->id,
        ]);

        // Resident posts a reply.
        $service->postMessage($conversation, $resident->id, 'Yes, slots B-12 and B-13 are free.');

        $this->assertEquals(2, Message::where('conversation_id', $conversation->id)->count());

        $conversation->refresh();
        $this->assertNotNull($conversation->last_message_at);
    }

    // -------------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------------

    public function test_broadcasts_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/communication/broadcasts', [
            'title'    => 'Alpha Only Broadcast',
            'message'  => 'Secret alpha message.',
            'channels' => ['email'],
            'audience' => 'all',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        // Beta admin must not see Alpha's broadcast (tenant scope).
        $this->flushSession();
        $this->actingAs($this->admin($beta))
            ->get('/communication')
            ->assertOk()
            ->assertDontSee('Alpha Only Broadcast');

        $this->assertEquals(1, Broadcast::withoutGlobalScopes()->count());
    }

    // -------------------------------------------------------------------------
    // Resident permission denied for broadcast
    // -------------------------------------------------------------------------

    public function test_resident_cannot_send_broadcasts(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->post('/communication/broadcasts', [
                'title'    => 'Resident Broadcast Attempt',
                'message'  => 'This should fail.',
                'channels' => ['email'],
                'audience' => 'all',
            ])
            ->assertForbidden();
    }
}

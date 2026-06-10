<?php

declare(strict_types=1);

namespace Tests\Feature\Notices;

use App\Models\Notice;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_and_publish_a_notice(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        // Create
        $response = $this->actingAs($admin)->post('/notices', [
            'title'    => 'Important Maintenance Notice',
            'body'     => 'Water will be cut off tomorrow.',
            'category' => 'notice',
            'audience' => 'all',
        ]);

        $notice = Notice::first();
        $this->assertNotNull($notice);
        $response->assertRedirect("/notices/{$notice->id}");
        $this->assertFalse((bool) $notice->is_published);
        $this->assertEquals($admin->id, $notice->author_id);

        // Publish
        $this->actingAs($admin)->post("/notices/{$notice->id}/publish");
        $notice->refresh();
        $this->assertTrue((bool) $notice->is_published);
        $this->assertNotNull($notice->published_at);
    }

    public function test_resident_sees_published_notices_but_not_drafts(): void
    {
        $society  = $this->makeSociety('Beta Society', 'beta@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        // Create a published notice
        Notice::create([
            'society_id'   => $society->id,
            'title'        => 'Published Notice',
            'body'         => 'This is published.',
            'category'     => 'notice',
            'audience'     => 'all',
            'author_id'    => $admin->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Create a draft notice
        Notice::create([
            'society_id'   => $society->id,
            'title'        => 'Draft Notice',
            'body'         => 'This is a draft.',
            'category'     => 'notice',
            'audience'     => 'all',
            'author_id'    => $admin->id,
            'is_published' => false,
        ]);

        $response = $this->actingAs($resident)->get('/notices');
        $response->assertOk();
        $response->assertSee('Published Notice');
        $response->assertDontSee('Draft Notice');
    }

    public function test_resident_can_vote_on_poll_but_cannot_vote_twice(): void
    {
        $society  = $this->makeSociety('Gamma Society', 'gamma@test.com');
        $admin    = $this->admin($society);
        $resident = $this->makeUser($society, 'resident');

        $notice = Notice::create([
            'society_id'   => $society->id,
            'title'        => 'AGM Poll Notice',
            'body'         => 'Please vote.',
            'category'     => 'announcement',
            'audience'     => 'all',
            'author_id'    => $admin->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $poll = Poll::create([
            'society_id'      => $society->id,
            'notice_id'       => $notice->id,
            'question'        => 'Preferred date?',
            'multiple_choice' => false,
            'is_active'       => true,
            'created_by'      => $admin->id,
        ]);

        $opt1 = PollOption::create(['society_id' => $society->id, 'poll_id' => $poll->id, 'label' => 'Saturday', 'votes_count' => 0]);
        $opt2 = PollOption::create(['society_id' => $society->id, 'poll_id' => $poll->id, 'label' => 'Sunday',   'votes_count' => 0]);

        // First vote — should succeed
        $this->actingAs($resident)
            ->post("/polls/{$poll->id}/vote", ['option_ids' => [$opt1->id]])
            ->assertRedirect();

        $opt1->refresh();
        $this->assertEquals(1, $opt1->votes_count);

        // Second vote — should be redirected back with error
        $response = $this->actingAs($resident)
            ->post("/polls/{$poll->id}/vote", ['option_ids' => [$opt2->id]]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Votes count must not increase
        $opt2->refresh();
        $this->assertEquals(0, $opt2->votes_count);
    }

    public function test_notices_are_isolated_between_tenants(): void
    {
        $alpha     = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $alphaAdmin = $this->admin($alpha);

        Notice::create([
            'society_id'   => $alpha->id,
            'title'        => 'Alpha Exclusive Notice',
            'body'         => 'Only Alpha can see this.',
            'category'     => 'notice',
            'audience'     => 'all',
            'author_id'    => $alphaAdmin->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $beta     = $this->makeSociety('Beta Society', 'beta@test.com');
        $betaAdmin = $this->admin($beta);

        // Flush session before cross-tenant request to prevent session leakage.
        $this->flushSession();

        $this->actingAs($betaAdmin)
            ->get('/notices')
            ->assertOk()
            ->assertDontSee('Alpha Exclusive Notice');

        $this->assertEquals(1, Notice::withoutGlobalScopes()->count());
    }

    public function test_resident_cannot_create_a_notice(): void
    {
        $society  = $this->makeSociety('Delta Society', 'delta@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->post('/notices', [
                'title'    => 'Resident notice attempt',
                'body'     => 'Should be denied.',
                'category' => 'notice',
                'audience' => 'all',
            ])
            ->assertForbidden();

        $this->assertEquals(0, Notice::count());
    }
}

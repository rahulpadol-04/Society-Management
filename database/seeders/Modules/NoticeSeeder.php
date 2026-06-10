<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Notice;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        $admin    = User::withoutGlobalScopes()->where('email', 'admin@greenvalley.test')->first();
        $resident = User::withoutGlobalScopes()->where('email', 'resident@greenvalley.test')->first();

        if (Notice::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        // 1. Pinned general notice
        Notice::create([
            'title'        => 'Welcome to Green Valley Society Portal',
            'body'         => "Dear Residents,\n\nWelcome to the Green Valley Society online portal. You can use this platform to stay updated with all notices, announcements, events and circulars issued by the management committee.\n\nFor any queries, please contact the secretary.",
            'category'     => 'notice',
            'author_id'    => $admin?->id,
            'audience'     => 'all',
            'is_published' => true,
            'published_at' => now()->subDays(30),
            'pinned'       => true,
        ]);

        // 2. Announcement with a poll (created below)
        $announcement = Notice::create([
            'title'        => 'Annual General Meeting — Date Selection',
            'body'         => "Dear Owners,\n\nThe Annual General Meeting (AGM) is scheduled for this quarter. Please vote on your preferred date so the committee can finalise the venue and agenda.",
            'category'     => 'announcement',
            'author_id'    => $admin?->id,
            'audience'     => 'owners',
            'is_published' => true,
            'published_at' => now()->subDays(10),
            'pinned'       => false,
        ]);

        // 3. Circular
        Notice::create([
            'title'        => 'Water Supply Interruption — Maintenance Work',
            'body'         => "Please be informed that water supply to all floors will be interrupted on Saturday between 10:00 AM and 2:00 PM due to routine maintenance of the overhead tank.\n\nKindly store sufficient water in advance.",
            'category'     => 'circular',
            'author_id'    => $admin?->id,
            'audience'     => 'all',
            'is_published' => true,
            'published_at' => now()->subDays(5),
            'pinned'       => false,
        ]);

        // 4. Event — future
        Notice::create([
            'title'        => 'Diwali Celebration — Community Get-Together',
            'body'         => "The society is organising a Diwali celebration in the community hall. All residents and their families are cordially invited.\n\nLight refreshments will be served.",
            'category'     => 'event',
            'author_id'    => $admin?->id,
            'audience'     => 'all',
            'is_published' => true,
            'published_at' => now()->subDays(3),
            'pinned'       => false,
            'event_at'     => now()->addDays(14),
        ]);

        // 5. Another notice
        Notice::create([
            'title'        => 'Parking Rules Update — Effective Immediately',
            'body'         => "As resolved in the last committee meeting, double-parking in the basement is strictly prohibited. Vehicles found violating this rule will be reported to the security guard for further action.",
            'category'     => 'notice',
            'author_id'    => $admin?->id,
            'audience'     => 'all',
            'is_published' => true,
            'published_at' => now()->subDays(7),
            'pinned'       => false,
        ]);

        // 6. Draft — not published
        Notice::create([
            'title'        => 'Draft: Proposed Changes to Maintenance Charges',
            'body'         => "This is a draft notice for internal review only. The committee is considering a 10% revision in monthly maintenance charges for the next financial year.",
            'category'     => 'notice',
            'author_id'    => $admin?->id,
            'audience'     => 'all',
            'is_published' => false,
            'pinned'       => false,
        ]);

        // Create poll attached to the announcement
        $poll = Poll::create([
            'society_id'      => $society->id,
            'notice_id'       => $announcement->id,
            'question'        => 'Which date works best for the AGM?',
            'description'     => 'Please select your preferred date for the Annual General Meeting.',
            'multiple_choice' => false,
            'closes_at'       => now()->addDays(7),
            'is_active'       => true,
            'created_by'      => $admin?->id,
        ]);

        $opt1 = PollOption::create(['society_id' => $society->id, 'poll_id' => $poll->id, 'label' => 'Saturday, 15th July', 'votes_count' => 0]);
        $opt2 = PollOption::create(['society_id' => $society->id, 'poll_id' => $poll->id, 'label' => 'Sunday, 16th July',   'votes_count' => 0]);
        $opt3 = PollOption::create(['society_id' => $society->id, 'poll_id' => $poll->id, 'label' => 'Saturday, 22nd July', 'votes_count' => 0]);

        // Seed a few demo votes from the resident
        if ($resident) {
            PollVote::create([
                'society_id'     => $society->id,
                'poll_id'        => $poll->id,
                'poll_option_id' => $opt2->id,
                'user_id'        => $resident->id,
            ]);
            $opt2->increment('votes_count');
        }

        // Seed an extra vote from admin as well
        if ($admin) {
            PollVote::create([
                'society_id'     => $society->id,
                'poll_id'        => $poll->id,
                'poll_option_id' => $opt1->id,
                'user_id'        => $admin->id,
            ]);
            $opt1->increment('votes_count');
        }

        tenancy()->forget();
    }
}

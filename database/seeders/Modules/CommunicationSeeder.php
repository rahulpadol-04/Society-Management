<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\Broadcast;
use App\Models\BroadcastRecipient;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunicationSeeder extends Seeder
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

        // -----------------------------------------------------------------------
        // Idempotent guard — skip if data already exists for this society.
        // -----------------------------------------------------------------------
        if (MessageTemplate::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        // -----------------------------------------------------------------------
        // 4 Message Templates
        // -----------------------------------------------------------------------
        $templates = [
            [
                'name'      => 'Welcome Email',
                'channel'   => 'email',
                'subject'   => 'Welcome to {{ society_name }}!',
                'body'      => "Dear {{ name }},\n\nWelcome to {{ society_name }}. Your account is ready.\n\nRegards,\nManagement",
                'variables' => ['name', 'society_name'],
                'is_active' => true,
            ],
            [
                'name'      => 'Maintenance Reminder SMS',
                'channel'   => 'sms',
                'subject'   => null,
                'body'      => "Reminder: Your maintenance bill of ₹{{ amount }} is due on {{ due_date }}. Please pay promptly.",
                'variables' => ['amount', 'due_date'],
                'is_active' => true,
            ],
            [
                'name'      => 'Event Announcement Push',
                'channel'   => 'push',
                'subject'   => '📣 Upcoming Event: {{ event_name }}',
                'body'      => "Join us for {{ event_name }} on {{ event_date }} at {{ venue }}. See you there!",
                'variables' => ['event_name', 'event_date', 'venue'],
                'is_active' => true,
            ],
            [
                'name'      => 'Dues Reminder WhatsApp',
                'channel'   => 'whatsapp',
                'subject'   => null,
                'body'      => "Hello {{ name }}, your society dues of ₹{{ amount }} are overdue. Kindly pay at the earliest to avoid penalties.",
                'variables' => ['name', 'amount'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $tpl) {
            MessageTemplate::create(array_merge($tpl, ['society_id' => $society->id]));
        }

        // -----------------------------------------------------------------------
        // Broadcast 1: Sent broadcast to residents
        // -----------------------------------------------------------------------
        $sentBroadcast = Broadcast::create([
            'society_id'       => $society->id,
            'title'            => 'Annual General Meeting – Date Announcement',
            'message'          => 'Dear Residents, the Annual General Meeting will be held on 15 July 2026 at 6:00 PM in the community hall. Attendance is mandatory.',
            'channels'         => ['email', 'push'],
            'audience'         => 'residents',
            'status'           => 'sent',
            'sent_at'          => now()->subDays(3),
            'recipients_count' => 0,
            'created_by'       => $admin?->id,
        ]);

        // Add recipient rows for demo (resident, if exists)
        if ($resident) {
            foreach (['email', 'push'] as $channel) {
                BroadcastRecipient::create([
                    'society_id'   => $society->id,
                    'broadcast_id' => $sentBroadcast->id,
                    'user_id'      => $resident->id,
                    'channel'      => $channel,
                    'status'       => 'sent',
                    'sent_at'      => now()->subDays(3),
                ]);
            }
            $sentBroadcast->update(['recipients_count' => 1]);
        }

        // -----------------------------------------------------------------------
        // Broadcast 2: Draft
        // -----------------------------------------------------------------------
        Broadcast::create([
            'society_id'       => $society->id,
            'title'            => 'Water Supply Interruption Notice',
            'message'          => 'Please note that water supply will be interrupted on 10 June 2026 from 9:00 AM to 1:00 PM due to pipeline maintenance.',
            'channels'         => ['sms', 'whatsapp'],
            'audience'         => 'all',
            'status'           => 'draft',
            'sent_at'          => null,
            'recipients_count' => 0,
            'created_by'       => $admin?->id,
        ]);

        // -----------------------------------------------------------------------
        // Broadcast 3: Queued (optional third broadcast)
        // -----------------------------------------------------------------------
        Broadcast::create([
            'society_id'       => $society->id,
            'title'            => 'Diwali Celebration Invite',
            'message'          => 'The management is happy to invite all residents to the Diwali celebration on 20 October 2026.',
            'channels'         => ['email', 'push', 'whatsapp'],
            'audience'         => 'all',
            'status'           => 'draft',
            'sent_at'          => null,
            'recipients_count' => 0,
            'created_by'       => $admin?->id,
        ]);

        // -----------------------------------------------------------------------
        // 1 Internal conversation: admin ↔ resident
        // -----------------------------------------------------------------------
        if ($admin && $resident) {
            $conversation = Conversation::create([
                'society_id'      => $society->id,
                'subject'         => 'Parking slot query',
                'type'            => 'direct',
                'created_by'      => $resident->id,
                'last_message_at' => now()->subHours(2),
            ]);

            foreach ([$admin->id, $resident->id] as $uid) {
                ConversationParticipant::create([
                    'society_id'      => $society->id,
                    'conversation_id' => $conversation->id,
                    'user_id'         => $uid,
                ]);
            }

            Message::create([
                'society_id'      => $society->id,
                'conversation_id' => $conversation->id,
                'user_id'         => $resident->id,
                'body'            => 'Hello, I wanted to enquire about the availability of visitor parking slots this weekend.',
                'created_at'      => now()->subHours(4),
            ]);

            Message::create([
                'society_id'      => $society->id,
                'conversation_id' => $conversation->id,
                'user_id'         => $admin->id,
                'body'            => 'Hi! Yes, slots B-12 and B-13 are available. Please register your guests at the gate office.',
                'created_at'      => now()->subHours(2),
            ]);
        }

        tenancy()->forget();
    }
}

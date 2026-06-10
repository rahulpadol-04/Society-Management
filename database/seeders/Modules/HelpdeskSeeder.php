<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\EscalationRule;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        $resident  = User::withoutGlobalScopes()->where('email', 'resident@greenvalley.test')->first();
        $admin     = User::withoutGlobalScopes()->where('email', 'admin@greenvalley.test')->first();
        $subAdmin  = User::withoutGlobalScopes()->where('email', 'subadmin@greenvalley.test')->first();

        // Guard: avoid re-seeding
        if (SupportTicket::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        // ----------------------------------------------------------------
        // Escalation Matrix (3 levels)
        // ----------------------------------------------------------------
        $escalationData = [
            ['level' => 1, 'name' => 'L1 – First Response',   'after_hours' => 24, 'notify_role' => 'sub-admin',     'is_active' => true],
            ['level' => 2, 'name' => 'L2 – Management Alert',  'after_hours' => 48, 'notify_role' => 'society-admin',  'is_active' => true],
            ['level' => 3, 'name' => 'L3 – Critical Escalation','after_hours' => 72, 'notify_role' => 'society-admin', 'is_active' => true],
        ];

        foreach ($escalationData as $row) {
            EscalationRule::firstOrCreate(
                ['society_id' => $society->id, 'level' => $row['level']],
                $row
            );
        }

        // ----------------------------------------------------------------
        // Sample tickets
        // ----------------------------------------------------------------
        $tickets = [
            [
                'subject'     => 'Unable to access the society portal',
                'description' => 'I cannot log in to the resident portal. The page shows an error 403.',
                'category'    => 'technical',
                'priority'    => 'high',
                'status'      => 'open',
                'raised_by'   => $resident?->id,
                'assigned_to' => null,
                'created_at'  => now()->subDays(3),
            ],
            [
                'subject'     => 'Maintenance fee payment not reflected',
                'description' => 'I paid the maintenance fee 5 days ago but it still shows as pending in my account.',
                'category'    => 'billing',
                'priority'    => 'urgent',
                'status'      => 'in_progress',
                'raised_by'   => $resident?->id,
                'assigned_to' => $admin?->id,
                'created_at'  => now()->subDays(5),
            ],
            [
                'subject'     => 'Swimming pool access card not working',
                'description' => 'My access card stopped working at the pool gate since last Thursday.',
                'category'    => 'facility',
                'priority'    => 'medium',
                'status'      => 'on_hold',
                'raised_by'   => $resident?->id,
                'assigned_to' => $subAdmin?->id,
                'created_at'  => now()->subDays(7),
            ],
            [
                'subject'     => 'Query about society by-laws',
                'description' => 'I would like to request a copy of the updated society by-laws document.',
                'category'    => 'general',
                'priority'    => 'low',
                'status'      => 'resolved',
                'raised_by'   => $resident?->id,
                'assigned_to' => $admin?->id,
                'created_at'  => now()->subDays(10),
            ],
            [
                'subject'     => 'Suspicious activity near parking Block B',
                'description' => 'Noticed an unknown vehicle parked for 3 days without any resident sticker.',
                'category'    => 'security',
                'priority'    => 'high',
                'status'      => 'closed',
                'raised_by'   => $resident?->id,
                'assigned_to' => $admin?->id,
                'created_at'  => now()->subDays(15),
            ],
            [
                'subject'     => 'Request to update registered mobile number',
                'description' => 'I recently changed my mobile number. Please update the same in society records.',
                'category'    => 'account',
                'priority'    => 'low',
                'status'      => 'open',
                'raised_by'   => $resident?->id,
                'assigned_to' => null,
                'created_at'  => now()->subDays(1),
            ],
        ];

        foreach ($tickets as $row) {
            $ref = 'TKT-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            while (SupportTicket::withTrashed()->where('ticket_number', $ref)->exists()) {
                $ref = 'TKT-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            }

            $ticket = SupportTicket::create([
                'ticket_number'    => $ref,
                'subject'          => $row['subject'],
                'description'      => $row['description'],
                'category'         => $row['category'],
                'priority'         => $row['priority'],
                'status'           => $row['status'],
                'raised_by'        => $row['raised_by'],
                'assigned_to'      => $row['assigned_to'],
                'sla_due_at'       => $row['created_at']->copy()->addHours(48),
                'sla_breached'     => $row['created_at']->addHours(48)->isPast() && ! in_array($row['status'], ['resolved', 'closed']),
                'escalation_level' => 0,
                'resolved_at'      => in_array($row['status'], ['resolved', 'closed']) ? now()->subDays(rand(0, 3)) : null,
                'closed_at'        => $row['status'] === 'closed' ? now()->subDays(rand(0, 2)) : null,
                'created_at'       => $row['created_at'],
                'updated_at'       => $row['created_at'],
            ]);

            TicketActivity::create([
                'society_id'        => $society->id,
                'support_ticket_id' => $ticket->id,
                'user_id'           => $row['raised_by'],
                'action'            => 'created',
                'note'              => 'Ticket registered (seed)',
                'created_at'        => $row['created_at'],
            ]);

            // Add an assignment activity and a reply for assigned tickets
            if ($row['assigned_to']) {
                TicketActivity::create([
                    'society_id'        => $society->id,
                    'support_ticket_id' => $ticket->id,
                    'user_id'           => $row['assigned_to'],
                    'action'            => 'assigned',
                    'note'              => 'Assigned to staff (seed)',
                    'created_at'        => $row['created_at']->copy()->addHours(2),
                ]);

                TicketReply::create([
                    'society_id'        => $society->id,
                    'support_ticket_id' => $ticket->id,
                    'user_id'           => $row['assigned_to'],
                    'message'           => 'We have received your ticket and are looking into it. You will hear from us shortly.',
                    'is_internal'       => false,
                    'created_at'        => $row['created_at']->copy()->addHours(3),
                ]);

                // Add an internal note for in_progress/on_hold tickets
                if (in_array($row['status'], ['in_progress', 'on_hold'])) {
                    TicketReply::create([
                        'society_id'        => $society->id,
                        'support_ticket_id' => $ticket->id,
                        'user_id'           => $row['assigned_to'],
                        'message'           => 'Internal: Awaiting vendor confirmation before proceeding.',
                        'is_internal'       => true,
                        'created_at'        => $row['created_at']->copy()->addHours(6),
                    ]);
                }
            }

            // Add resolved/closed activities
            if (in_array($row['status'], ['resolved', 'closed'])) {
                TicketActivity::create([
                    'society_id'        => $society->id,
                    'support_ticket_id' => $ticket->id,
                    'user_id'           => $row['assigned_to'],
                    'action'            => 'status_changed',
                    'from_status'       => 'in_progress',
                    'to_status'         => $row['status'],
                    'note'              => 'Issue resolved and verified (seed)',
                    'created_at'        => $ticket->resolved_at ?? now(),
                ]);
            }
        }

        tenancy()->forget();
    }
}

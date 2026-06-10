<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['open', 'in_progress', 'on_hold', 'resolved', 'closed']);

        return [
            'ticket_number'    => 'TKT-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'subject'          => $this->faker->sentence(5),
            'description'      => $this->faker->paragraph(),
            'category'         => $this->faker->randomElement(['general', 'technical', 'billing', 'facility', 'security', 'account', 'other']),
            'priority'         => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status'           => $status,
            'sla_due_at'       => now()->addHours(48),
            'sla_breached'     => false,
            'escalation_level' => 0,
            'resolved_at'      => in_array($status, ['resolved', 'closed']) ? now() : null,
            'closed_at'        => $status === 'closed' ? now() : null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => 'open', 'resolved_at' => null, 'closed_at' => null]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status'      => 'closed',
            'resolved_at' => now(),
            'closed_at'   => now(),
        ]);
    }
}

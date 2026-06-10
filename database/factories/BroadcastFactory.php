<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Broadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

class BroadcastFactory extends Factory
{
    protected $model = Broadcast::class;

    public function definition(): array
    {
        $status   = $this->faker->randomElement(['draft', 'queued', 'sent', 'failed']);
        $audience = $this->faker->randomElement(['all', 'owners', 'tenants', 'staff', 'residents']);
        $channels = $this->faker->randomElements(['email', 'sms', 'whatsapp', 'push', 'in_app'], rand(1, 3));

        return [
            'title'            => $this->faker->sentence(5),
            'message'          => $this->faker->paragraph(),
            'channels'         => $channels,
            'audience'         => $audience,
            'status'           => $status,
            'scheduled_at'     => null,
            'sent_at'          => $status === 'sent' ? now() : null,
            'recipients_count' => $status === 'sent' ? $this->faker->numberBetween(10, 200) : 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attr) => [
            'status'           => 'sent',
            'sent_at'          => now(),
            'recipients_count' => $this->faker->numberBetween(5, 100),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attr) => [
            'status'           => 'draft',
            'sent_at'          => null,
            'recipients_count' => 0,
        ]);
    }
}

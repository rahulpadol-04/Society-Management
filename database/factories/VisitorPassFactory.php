<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VisitorPass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VisitorPassFactory extends Factory
{
    protected $model = VisitorPass::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'approved', 'rejected', 'used']);

        return [
            'code'           => 'VP-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'name'           => $this->faker->name(),
            'phone'          => $this->faker->phoneNumber(),
            'type'           => $this->faker->randomElement(['guest', 'delivery', 'cab', 'service', 'vendor']),
            'purpose'        => $this->faker->sentence(3),
            'vehicle_number' => $this->faker->boolean(40) ? strtoupper($this->faker->bothify('??##??####')) : null,
            'expected_at'    => $this->faker->dateTimeBetween('now', '+7 days'),
            'valid_until'    => $this->faker->dateTimeBetween('+1 day', '+14 days'),
            'max_entries'    => $this->faker->randomElement([1, 2, 3]),
            'entries_used'   => 0,
            'status'         => $status,
            'approved_at'    => $status === 'approved' ? now() : null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status'      => 'pending',
            'approved_at' => null,
        ]);
    }
}

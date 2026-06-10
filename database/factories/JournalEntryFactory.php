<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'posted']);

        return [
            'reference'  => 'JE-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'entry_date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'narration'  => $this->faker->sentence(6),
            'type'       => $this->faker->randomElement(['journal', 'income', 'expense', 'transfer']),
            'status'     => $status,
            'amount'     => $this->faker->randomFloat(2, 1000, 50000),
            'source'     => 'manual',
            'posted_at'  => $status === 'posted' ? now() : null,
        ];
    }

    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'    => 'posted',
            'posted_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'    => 'draft',
            'posted_at' => null,
        ]);
    }
}

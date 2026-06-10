<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        $moveIn = $this->faker->dateTimeBetween('-3 years', 'now');

        return [
            'type'         => $this->faker->randomElement(['owner', 'tenant']),
            'name'         => $this->faker->name(),
            'email'        => $this->faker->unique()->safeEmail(),
            'phone'        => $this->faker->phoneNumber(),
            'relation'     => null,
            'is_primary'   => false,
            'photo'        => null,
            'move_in_date' => $moveIn,
            'status'       => 'active',
            'meta'         => null,
        ];
    }

    public function owner(): static
    {
        return $this->state(['type' => 'owner', 'is_primary' => true]);
    }

    public function tenant(): static
    {
        return $this->state(['type' => 'tenant']);
    }

    public function familyMember(int $parentId): static
    {
        return $this->state([
            'type'      => 'family_member',
            'parent_id' => $parentId,
            'relation'  => $this->faker->randomElement(['spouse', 'child', 'parent', 'sibling']),
        ]);
    }
}

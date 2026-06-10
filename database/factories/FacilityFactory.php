<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->randomElement(['Clubhouse', 'Gym', 'Swimming Pool', 'Tennis Court', 'Community Hall', 'Badminton Court']),
            'type'              => $this->faker->randomElement(['clubhouse', 'gym', 'pool', 'court', 'hall', 'other']),
            'description'       => $this->faker->sentence(8),
            'capacity'          => $this->faker->optional()->numberBetween(10, 100),
            'charge'            => $this->faker->randomElement([0, 100, 200, 500, 1000]),
            'requires_approval' => $this->faker->boolean(70),
            'opening_time'      => '06:00',
            'closing_time'      => '22:00',
            'slot_minutes'      => $this->faker->randomElement([60, 90, 120]),
            'is_active'         => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function free(): static
    {
        return $this->state(['charge' => 0, 'requires_approval' => false]);
    }
}

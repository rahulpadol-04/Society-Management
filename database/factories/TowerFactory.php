<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Society;
use App\Models\Tower;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tower> */
class TowerFactory extends Factory
{
    protected $model = Tower::class;

    public function definition(): array
    {
        $letter = fake()->unique()->randomLetter();

        return [
            'society_id'      => Society::factory(),
            'name'            => 'Tower '.strtoupper($letter),
            'code'            => strtoupper($letter),
            'type'            => 'tower',
            'total_floors'    => fake()->numberBetween(4, 20),
            'units_per_floor' => fake()->numberBetween(2, 6),
            'status'          => 'active',
        ];
    }
}

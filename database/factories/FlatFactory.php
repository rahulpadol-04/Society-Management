<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Flat;
use App\Models\Society;
use App\Models\Tower;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Flat> */
class FlatFactory extends Factory
{
    protected $model = Flat::class;

    public function definition(): array
    {
        return [
            'society_id'   => Society::factory(),
            'tower_id'     => Tower::factory(),
            'number'       => strtoupper(fake()->randomLetter()).'-'.fake()->unique()->numberBetween(101, 999),
            'type'         => fake()->randomElement(['1BHK', '2BHK', '3BHK']),
            'carpet_area'  => fake()->numberBetween(450, 1800),
            'ownership'    => fake()->randomElement(['owner_occupied', 'rented']),
            'status'       => fake()->randomElement(['occupied', 'vacant', 'on_rent']),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorLogFactory extends Factory
{
    protected $model = VisitorLog::class;

    public function definition(): array
    {
        $checkedIn  = $this->faker->dateTimeBetween('-14 days', 'now');
        $status     = $this->faker->randomElement(['in', 'out']);
        $checkedOut = $status === 'out'
            ? $this->faker->dateTimeBetween($checkedIn, 'now')
            : null;

        return [
            'name'           => $this->faker->name(),
            'phone'          => $this->faker->phoneNumber(),
            'type'           => $this->faker->randomElement(['guest', 'delivery', 'cab', 'service', 'vendor']),
            'purpose'        => $this->faker->sentence(3),
            'vehicle_number' => null,
            'gate'           => $this->faker->randomElement(['Main Gate', 'Side Gate', null]),
            'checked_in_at'  => $checkedIn,
            'checked_out_at' => $checkedOut,
            'status'         => $status,
        ];
    }
}

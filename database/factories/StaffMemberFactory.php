<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffMemberFactory extends Factory
{
    protected $model = StaffMember::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->name(),
            'employee_code' => 'EMP-'.strtoupper($this->faker->unique()->bothify('####')),
            'designation'   => $this->faker->jobTitle(),
            'department'    => $this->faker->randomElement(['security', 'housekeeping', 'maintenance', 'admin', 'gardening', 'plumbing', 'electrical', 'other']),
            'phone'         => $this->faker->phoneNumber(),
            'email'         => $this->faker->unique()->safeEmail(),
            'joining_date'  => $this->faker->dateTimeBetween('-3 years', '-1 month')->format('Y-m-d'),
            'salary'        => $this->faker->randomElement([12000, 15000, 18000, 20000, 25000, 30000]),
            'shift'         => $this->faker->randomElement(['morning', 'evening', 'night', 'general']),
            'status'        => 'active',
            'address'       => $this->faker->address(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}

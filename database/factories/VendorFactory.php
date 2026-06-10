<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name'           => $this->faker->company(),
            'company'        => $this->faker->optional()->company(),
            'category'       => $this->faker->randomElement([
                'plumbing', 'electrical', 'housekeeping', 'security',
                'landscaping', 'elevator', 'pest_control', 'general', 'other',
            ]),
            'contact_person' => $this->faker->name(),
            'phone'          => $this->faker->phoneNumber(),
            'email'          => $this->faker->companyEmail(),
            'gstin'          => $this->faker->optional()->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
            'address'        => $this->faker->address(),
            'rating'         => 0.0,
            'ratings_count'  => 0,
            'status'         => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
            'notes'          => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function blacklisted(): static
    {
        return $this->state(['status' => 'blacklisted']);
    }
}

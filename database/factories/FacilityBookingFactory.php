<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FacilityBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityBookingFactory extends Factory
{
    protected $model = FacilityBooking::class;

    public function definition(): array
    {
        $start = $this->faker->randomElement(['08:00', '09:00', '10:00', '11:00', '14:00', '16:00', '18:00']);
        [$h, $m] = explode(':', $start);
        $end = sprintf('%02d:%02d', (int) $h + 1, $m);

        return [
            'booking_date' => $this->faker->dateTimeBetween('-7 days', '+14 days')->format('Y-m-d'),
            'start_time'   => $start,
            'end_time'     => $end,
            'guests'       => $this->faker->numberBetween(0, 5),
            'amount'       => $this->faker->randomElement([0, 100, 200, 500]),
            'status'       => $this->faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
            'notes'        => $this->faker->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['open', 'assigned', 'in_progress', 'resolved', 'closed']);

        return [
            'reference'   => 'CMP-'.now()->format('ym').'-'.Str::upper(Str::random(5)),
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'status'      => $status,
            'sla_due_at'  => now()->addHours(48),
            'resolved_at' => in_array($status, ['resolved', 'closed']) ? now() : null,
        ];
    }
}

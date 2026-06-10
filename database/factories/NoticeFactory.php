<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement(['notice', 'announcement', 'circular', 'event']);

        return [
            'title'        => $this->faker->sentence(5),
            'body'         => $this->faker->paragraphs(2, true),
            'category'     => $category,
            'audience'     => $this->faker->randomElement(['all', 'owners', 'tenants', 'staff']),
            'is_published' => true,
            'published_at' => now()->subDays(rand(0, 30)),
            'pinned'       => false,
            'event_at'     => $category === 'event' ? now()->addDays(rand(1, 30)) : null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false, 'published_at' => null]);
    }

    public function pinned(): static
    {
        return $this->state(['pinned' => true]);
    }
}

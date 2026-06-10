<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CmsPage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CmsPage>
 */
class CmsPageFactory extends Factory
{
    protected $model = CmsPage::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(4, true);

        return [
            'title'            => ucfirst($title),
            'slug'             => Str::slug($title),
            'content'          => fake()->paragraphs(3, true),
            'meta_title'       => ucfirst($title),
            'meta_description' => fake()->sentence(),
            'status'           => 'draft',
            'published_at'     => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'published',
            'published_at' => now(),
        ]);
    }
}

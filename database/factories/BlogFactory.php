<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(6, true);
        $categories = ['Product', 'Industry', 'Tips & Tricks', 'Case Study', 'News'];

        return [
            'title'        => ucfirst($title),
            'slug'         => Str::slug($title),
            'excerpt'      => fake()->paragraph(),
            'content'      => fake()->paragraphs(5, true),
            'cover_image'  => null,
            'author_id'    => null,
            'category'     => fake()->randomElement($categories),
            'status'       => 'draft',
            'published_at' => null,
            'views'        => 0,
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

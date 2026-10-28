<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $title = rtrim($this->faker->unique()->sentence(3), '.'),
            'slug' => null,
            'summary' => $this->faker->sentence(12),
            'description' => $this->faker->paragraphs(3, true),
            'technologies' => $this->faker->randomElements(
                ['Laravel', 'PHP', 'MySQL', 'Tailwind CSS', 'Alpine.js', 'Redis'],
                3,
            ),
            'category' => $this->faker->randomElement(['Web App', 'API', 'Dashboard']),
            'live_demo_url' => null,
            'repository_url' => null,
            'is_featured' => false,
            'is_demo' => false,
            'status' => ProjectStatus::Draft,
            'sort_order' => $this->faker->unique()->numberBetween(1, 999),
            'published_at' => null,
        ];
    }

    /**
     * Indicate that the project is live on the public portfolio.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Published,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the project was public and has been hidden again.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Unpublished,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the project is marked as showcase work.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the project is a template placeholder.
     */
    public function demo(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_demo' => true,
        ]);
    }
}

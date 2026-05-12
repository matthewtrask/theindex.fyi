<?php

namespace Database\Factories;

use App\Enums\Category;
use App\Enums\IndexStatus;
use App\Models\Index;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Index>
 */
class IndexFactory extends Factory
{
    protected $model = Index::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'url' => fake()->url(),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(Category::cases()),
            'accepts_submissions' => true,
            'language' => null,
            'status' => IndexStatus::Active,
            'last_checked_at' => now(),
            'submitted_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => IndexStatus::Inactive]);
    }

    public function dead(): static
    {
        return $this->state(['status' => IndexStatus::Dead]);
    }
}

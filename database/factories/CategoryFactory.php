<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'image_url' => fake()->imageUrl(),
            'image_credit' => 'Photo by '.fake()->name().' on Unsplash',
            'image_credit_href' => 'https://unsplash.com/@'.fake()->userName(),
            'position' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}

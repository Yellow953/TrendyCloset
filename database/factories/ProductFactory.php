<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));
        $price = fake()->randomFloat(2, 18, 80);
        $onSale = fake()->boolean(40);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->paragraph(),
            'price' => $price,
            'compare_at_price' => $onSale ? round($price * fake()->randomFloat(2, 1.1, 1.4), 2) : null,
            'badge' => fake()->optional()->randomElement(['NEW', 'HOT']),
            'rating' => fake()->numberBetween(4, 5),
            'is_featured' => fake()->boolean(30),
            'is_active' => true,
            'sale_ends_at' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function onDeal(): static
    {
        return $this->state(fn () => ['sale_ends_at' => now()->addDays(5)]);
    }
}

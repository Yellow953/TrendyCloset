<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => fake()->imageUrl(900, 1200),
            'credit' => 'Photo by '.fake()->name().' on Unsplash',
            'credit_href' => 'https://unsplash.com/@'.fake()->userName(),
            'is_primary' => false,
            'position' => fake()->numberBetween(0, 5),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'position' => 0]);
    }
}

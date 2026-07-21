<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(Str::random(8)),
            'size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', '2XL']),
            'color' => fake()->randomElement(['Sage', 'Ecru', 'Clay', 'Sand', 'Oat', 'Stone']),
            'price_override' => null,
            'stock' => fake()->numberBetween(0, 40),
            'is_active' => true,
        ];
    }
}

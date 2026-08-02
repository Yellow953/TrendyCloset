<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * A line snapshots the garment as it was bought, so these columns are
     * copied rather than joined. Callers pass a real variant; the defaults are
     * only here so the factory stands on its own.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 15, 180);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'product_variant_id' => null,
            'product_name' => fake()->words(3, true),
            'variant_size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'variant_color' => fake()->colorName(),
            'sku' => strtoupper(fake()->unique()->bothify('??-####')),
            'unit_price' => $price,
            'quantity' => $quantity,
            'line_total' => round($price * $quantity, 2),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => fake()->randomElement(['percent', 'fixed']),
            'value' => fake()->randomElement([10, 15, 20]),
            'min_subtotal' => null,
            'free_shipping' => false,
            'starts_at' => null,
            'expires_at' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => true,
        ];
    }
}

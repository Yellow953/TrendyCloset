<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Totals default to zero: an order is only worth what its lines say, and
     * the seeder sets them once the lines exist.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'customer_id' => null,
            'coupon_id' => null,
            'order_number' => 'TC-'.fake()->unique()->numerify('########-####'),
            'status' => OrderStatus::Pending,
            'email' => fake()->optional(0.6)->safeEmail(),
            'ship_name' => $name,
            'ship_phone' => '+961'.fake()->numerify('76######'),
            'ship_street' => fake()->streetName(),
            'ship_building' => 'Bldg '.fake()->numberBetween(1, 40),
            'ship_floor' => (string) fake()->numberBetween(1, 9),
            'ship_details' => null,
            'ship_city' => fake()->city(),
            'ship_region' => null,
            'ship_postcode' => null,
            'ship_country' => config('store.contact.country'),
            'subtotal' => 0,
            'discount_total' => 0,
            'shipping_total' => 0,
            'grand_total' => 0,
            'notes' => null,
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}

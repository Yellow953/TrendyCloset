<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+961'.fake()->unique()->numerify('76######'),
            'email' => fake()->optional(0.6)->safeEmail(),
            'marketing_opt_in' => fake()->boolean(40),
            'notes' => null,
        ];
    }
}

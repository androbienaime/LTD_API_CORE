<?php

namespace Database\Factories\Core;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "currency" => $this->faker->currencyCode(),
            "symbol" => $this->faker->currencyCode(),
            "exchange_rate" => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}

<?php

namespace Database\Factories\Core;

use App\Models\Core\Account;
use App\Models\Core\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Core\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "slug" => $this->faker->slug(),
        ];
    }

    public function configure(){
        return $this->afterCreating(function (Shop $shop){
            $shop->account()->attach(Account::factory()->count(1)->create()->pluck('id'), [
                "status" => "active"
            ]);
        });
    }
}

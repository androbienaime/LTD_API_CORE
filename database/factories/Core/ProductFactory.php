<?php

namespace Database\Factories\Core;

use App\Core\States\GeneralStatus\ActiveState;
use App\Models\Core\Account;
use App\Models\Core\Brand;
use App\Models\Core\Category;
use App\Models\Core\Currency;
use App\Models\Core\Product;
use App\Models\Core\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
{
    protected  $model = Product::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name(),
            "price" => $this->faker->randomFloat(2, 10),
            "slug" => $this->faker->slug(),
            "description" => $this->faker->text(),
            "sku" => $this->faker->unique()->randomNumber(),
            "product_type" => $this->faker->randomElement(["product", "service", "digital"]),
            "currency_id" => Currency::factory(),
            "shop_id" => Shop::factory(),
            "merchant_id" => Account::factory(),

        ];
    }

    public function configure() : static{
        return $this->afterCreating(function (Product $product){
            $product->categories()->attach(Category::factory());
            $product->brands()->attach(Brand::factory());

            for($i=0; $i < random_int(1, 5); $i++) {
                $product->addMediaFromUrl('https://via.placeholder.com/150')->toMediaCollection('images');
            }
        });
    }
}

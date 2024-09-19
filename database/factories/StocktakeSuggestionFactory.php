<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class StocktakeSuggestionFactory extends Factory
{
    public function definition(): array
    {
        $inventory = Product::factory()->create()->inventory()->first();

        return [
            'inventory_id' => $inventory->getKey(),
            'product_id' => $inventory->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'points' => $this->faker->randomNumber(),
            'reason' => $this->faker->text(),
        ];
    }
}

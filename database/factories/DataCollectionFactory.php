<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataCollectionFactory extends Factory
{
    public function definition(): array
    {
        $warehouse = Warehouse::first() ?? Warehouse::factory()->create();

        return [
            'warehouse_code' => $warehouse->code,
            'warehouse_id' => $warehouse->getKey(),
            'name' => $this->faker->word(),
        ];
    }
}

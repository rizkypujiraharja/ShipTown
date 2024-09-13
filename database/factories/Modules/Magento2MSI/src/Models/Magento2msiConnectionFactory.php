<?php

namespace Database\Factories\Modules\Magento2MSI\src\Models;

use App\Modules\Magento2MSI\src\Models\Magento2msiConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class Magento2msiConnectionFactory extends Factory
{
    protected $model = Magento2msiConnection::class;

    public function definition(): array
    {
        return [
            'base_url' => $this->faker->url(),
            'magento_source_code' => $this->faker->word(),
            'inventory_source_warehouse_tag_id' => $this->faker->randomNumber(),
            'api_access_token' => $this->faker->word(),
        ];
    }
}

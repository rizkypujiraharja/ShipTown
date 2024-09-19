<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'test_service',
            'service_provider_class' => '',
        ];
    }
}

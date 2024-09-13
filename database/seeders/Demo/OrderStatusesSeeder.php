<?php

namespace Database\Seeders\Demo;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusesSeeder extends Seeder
{
    public function run(): void
    {
        OrderStatus::updateOrCreate([
            'code' => 'complete',
        ], [
            'name' => 'complete',
            'order_active' => false,
            'order_on_hold' => false,
        ]);

        OrderStatus::updateOrCreate([
            'code' => 'paid',
        ], [
            'name' => 'paid',
            'order_active' => true,
            'order_on_hold' => false,
        ]);

        OrderStatus::updateOrCreate([
            'code' => 'picking',
        ], [
            'name' => 'picking',
            'order_active' => true,
            'order_on_hold' => false,
        ]);

        OrderStatus::updateOrCreate([
            'code' => 'refunded',
        ], [
            'name' => 'refunded',
            'order_active' => true,
            'order_on_hold' => false,
        ]);

        OrderStatus::updateOrCreate([
            'code' => 'returned',
        ], [
            'name' => 'returned',
            'order_active' => true,
            'order_on_hold' => true,
        ]);

        OrderStatus::updateOrCreate([
            'code' => 'out_of_stock',
        ], [
            'name' => 'out_of_stock',
            'order_active' => true,
            'order_on_hold' => true,
        ]);
    }
}

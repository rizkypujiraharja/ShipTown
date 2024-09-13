<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class UnpaidOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()->count(rand(1, 10))
            ->with('orderProducts', 1)
            ->create(['status_code' => 'processing']);

        Order::factory()->count(rand(1, 10))
            ->with('orderProducts', 2)
            ->create(['status_code' => 'processing']);

        Order::factory()->count(rand(1, 10))
            ->with('orderProducts', 4)
            ->create(['status_code' => 'processing']);
    }
}

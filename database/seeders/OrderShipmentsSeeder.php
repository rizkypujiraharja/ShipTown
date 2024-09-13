<?php

namespace Database\Seeders;

use App\Models\OrderShipment;
use Illuminate\Database\Seeder;

class OrderShipmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderShipment::factory()->count(rand(100, 200))->create();
    }
}

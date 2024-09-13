<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class FulfilmentCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var Warehouse $fulfilmentCenter */
        $fulfilmentCenter = Warehouse::firstOrCreate(['code' => '99'], ['name' => 'Warehouse']);

        $fulfilmentCenter->attachTag('fulfilment');
    }
}

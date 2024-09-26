<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class BigDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory(20)->create();

        $i = 1000;

        while ($i > 0) {
            ProductsManySeeder::createRecords(500);
            $this->call([
                InventorySeeder::class,
            ]);
            $i--;
        }
    }
}

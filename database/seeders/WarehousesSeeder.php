<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::firstOrCreate(['code' => 'WEB'], ['name' => 'Website Orders'])->attachTag('ALL');

        Warehouse::firstOrCreate(['code' => 'WHS'], ['name' => 'Warehouse'])->attachTag('ALL')->attachTag('fulfilment');
        Warehouse::firstOrCreate(['code' => 'DUB'], ['name' => 'Dublin'])->attachTag('ALL');
        Warehouse::firstOrCreate(['code' => 'CRK'], ['name' => 'Cork'])->attachTag('ALL');
        Warehouse::firstOrCreate(['code' => 'GAL'], ['name' => 'Galway'])->attachTag('ALL');
    }
}

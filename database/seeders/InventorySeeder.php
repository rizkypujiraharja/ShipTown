<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movements = Inventory::query()
            ->with('prices')
            ->where('warehouse_code', '!=', DB::raw('999'))
            ->inRandomOrder()
            ->limit(1000)
            ->get()
            ->map(function (Inventory $inventory) {
                $restock_level = fake()->randomElement([1, 6, 6, 6, 12, 12, 12, 12, 24, 24, 24, 36, 72]);
                $random_location = Str::upper(fake()->randomLetter.fake()->randomNumber(2));
                $inventory->updateQuietly([
                    'restock_level' => $restock_level,
                    'reorder_point' => round($restock_level / 3),
                    'shelve_location' => $random_location,
                ]);

                return [
                    'occurred_at' => now()->subMonth(),
                    'inventory_id' => $inventory->getKey(),
                    'product_id' => $inventory->product_id,
                    'warehouse_code' => $inventory->warehouse_code,
                    'warehouse_id' => $inventory->warehouse_id,
                    'quantity_before' => 0,
                    'quantity_delta' => $inventory->restock_level,
                    'quantity_after' => $inventory->restock_level,
                    'unit_cost' => $inventory->prices->cost,
                    'unit_price' => $inventory->prices->price,
                    'type' => 'stocktake',
                    'description' => 'initial stocktake',
                    'created_at' => now()->subMonth(),
                ];
            });

        InventoryMovement::query()->insert($movements->toArray());
        //
        //        $product_id = Product::findBySKU('45')->getKey();
        //
        //        Inventory::query()
        //            ->where(['product_id' => $product_id])
        //            ->update(['shelve_location' => 'A1']);

        SequenceNumberJob::dispatch();
    }
}

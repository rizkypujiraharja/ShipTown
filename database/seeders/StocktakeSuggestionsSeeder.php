<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\StocktakeSuggestion;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StocktakeSuggestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::query()
            ->whereNotIn('code', ['999'])
            ->get()
            ->each(function (Warehouse $warehouse) {
                Inventory::query()
                    ->where(['warehouse_id' => $warehouse->getKey()])
                    ->inRandomOrder()
                    ->limit(10)
                    ->get()
                    ->each(function (Inventory $inventory) {
                        $stocktakeSuggestion = new StocktakeSuggestion;
                        $stocktakeSuggestion->product_id = $inventory->product_id;
                        $stocktakeSuggestion->inventory_id = $inventory->getKey();
                        $stocktakeSuggestion->warehouse_id = $inventory->warehouse_id;
                        $stocktakeSuggestion->points = 20;
                        $stocktakeSuggestion->reason = 'Manual stocktake request';
                        $stocktakeSuggestion->save();
                    });
            });
    }
}

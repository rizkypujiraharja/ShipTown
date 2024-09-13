<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class RestockingReportSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Warehouse $destination_warehouse */
        /** @var Warehouse $source_warehouse */
        $source_warehouse =  Warehouse::withAnyTagsOfAnyType('fulfilment')->first() ?? Warehouse::factory()->create()->attachTag('fulfilment');

        /** @var Warehouse $destination_warehouse */
        $destination_warehouse = Warehouse::whereCode('DUB')->first() ?? Warehouse::factory()->create(['code' => 'DUB']);

        $products = Product::factory()->count(10)->create();

        $productPrices = ProductPrice::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->each(fn(ProductPrice $productPrice) => $productPrice->update([
                'price' => rand(40, 100),
                'cost' => rand(5, 30),
            ]));

        $products->each(function (Product $product) use ($source_warehouse, $destination_warehouse) {
            /** @var Inventory $source_inventory */
            $source_inventory = $product->inventory($source_warehouse->code)->first();

            /** @var Inventory $source_inventory */
            $destination_inventory = $product->inventory($destination_warehouse->code)->first();

            InventoryService::adjust($source_inventory, rand(10, 50), [
                'description' => 'stocktake for Restocking report sample',
            ]);

            InventoryService::adjust($destination_inventory, rand(20, 30), [
                'description' => 'stocktake for Restocking report sample',
            ]);

            InventoryService::sell($destination_inventory, -rand(5, 20), [
                'description' => 'transaction #1234',
            ]);
        });

        Inventory::query()
            ->where(['warehouse_code' => $destination_warehouse->code])
            ->whereIn('product_id', $products->pluck('id'))
            ->eachById(fn(Inventory $inventory) => $inventory->update([
                'reorder_point' => rand(50, 70),
                'restock_level' => rand(70, 90),
            ]));
    }
}

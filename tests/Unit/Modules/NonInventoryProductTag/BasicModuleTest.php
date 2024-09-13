<?php

namespace Tests\Unit\Modules\NonInventoryProductTag;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\NonInventoryProductTag\src\Jobs\ClearArcadiaStockJob;
use App\Services\InventoryService;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    //    /** @test */
    //    public function test_module_basic_functionality()
    //    {
    //        NonInventoryProductTagServiceProvider::enableModule();
    //
    //        $warehouse = Warehouse::factory()->create();
    //        $product = Product::factory()->create();
    //
    //        $inventory = Inventory::firstOrCreate([
    //            'product_id' => $product->id,
    //            'warehouse_id' => $warehouse->id,
    //        ], []);
    //
    //        $inventory->product->attachTag('non-inventory');
    //
    //        InventoryService::adjustQuantity($inventory, 10, 'test');
    //
    //        $inventory->refresh();
    //
    //        $this->assertEquals(0, $inventory->quantity);
    //    }

    public function test_basic(): void
    {
        InventoryTotalsServiceProvider::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()
            ->where([
                'product_id' => $product->getKey(),
                'warehouse_id' => $warehouse->getKey(),
            ])
            ->first();

        /** @var Inventory $product2Inventory */
        $product2Inventory = $product2->inventory($warehouse->code)->first();

        InventoryService::adjust($inventory, 10);
        InventoryService::adjust($product2Inventory, 10);

        $product->attachTag('ARCADIA DEAL JAN 2023', 'rms_sub_description3');

        ClearArcadiaStockJob::dispatch();

        $inventory1 = Inventory::query()->where('quantity', '!=', 0);

        $this->assertEquals(1, $inventory1->count());
    }
}

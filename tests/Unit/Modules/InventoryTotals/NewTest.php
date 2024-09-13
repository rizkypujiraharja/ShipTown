<?php

namespace Tests\Unit\Modules\InventoryTotals;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryTotals\src\Jobs\RecalculateInventoryTotalsByWarehouseTagJob;
use App\Modules\InventoryTotals\src\Models\InventoryTotalByWarehouseTag;
use App\Services\InventoryService;
use Tests\TestCase;

class NewTest extends TestCase
{
    public function testBasic(): void
    {
        /** @var Warehouse $warehouse1 */
        $warehouse1 = Warehouse::factory()->create();
        /** @var Warehouse $warehouse2 */
        $warehouse2 = Warehouse::factory()->create();
        $warehouse3 = Warehouse::factory()->create();
        $warehouse4 = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::first();
        Inventory::query()->get()->each(function (Inventory $inventory) {
            InventoryService::adjust($inventory, 1);
        });

        $warehouse1->attachTag('tag1');
        $warehouse2->attachTag('tag1');

        RecalculateInventoryTotalsByWarehouseTagJob::dispatchSync();

        $inventoryTotalByWarehouseTag = InventoryTotalByWarehouseTag::first();

        $this->assertEquals(2, $inventoryTotalByWarehouseTag->quantity);
        ray(InventoryTotalByWarehouseTag::all()->toArray(), Inventory::all()->toArray());
    }
}

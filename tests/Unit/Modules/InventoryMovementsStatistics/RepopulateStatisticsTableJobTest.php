<?php

namespace Tests\Unit\Modules\InventoryMovementsStatistics;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Inventory\src\Jobs\DispatchRecalculateInventoryRecordsJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RecalculateStatisticsTableJob;
use Tests\TestCase;

class RepopulateStatisticsTableJobTest extends TestCase
{
    public function testBasic(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $warehouse = Warehouse::first();

        $inventory1 = Inventory::find($product1->getKey(), $warehouse->getKey());
        $inventory2 = Inventory::find($product2->getKey(), $warehouse->getKey());

        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'inventory_id' => $inventory1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'inventory_id' => $inventory1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product1->getKey(), 'inventory_id' => $inventory1->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);

        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'inventory_id' => $inventory2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'inventory_id' => $inventory2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);
        InventoryMovement::factory()->create(['product_id' => $product2->getKey(), 'inventory_id' => $inventory2->getKey(), 'type' => 'sale', 'occurred_at' => now()->subDays(2)]);

        SequenceNumberJob::dispatchSync();

        DispatchRecalculateInventoryRecordsJob::dispatchSync();

        RecalculateStatisticsTableJob::dispatch();

        $this->assertDatabaseCount('inventory_movements_statistics', 2);
    }
}

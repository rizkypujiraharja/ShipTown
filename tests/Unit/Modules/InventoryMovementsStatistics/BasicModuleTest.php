<?php

namespace Tests\Unit\Modules\InventoryMovementsStatistics;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryMovementsStatistics\src\Jobs\RemoveOutdatedSalesJob;
use App\Modules\InventoryMovementsStatistics\src\Models\InventoryMovementsStatistic;
use App\Services\InventoryService;
use Carbon\Carbon;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function test_if_sales_are_removed_from_statistics(): void
    {
        InventoryMovementsStatisticsServiceProvider::enableModule();

        Product::factory()->create();
        Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::first();

        $randomQuantity = rand(1, 100);

        Carbon::setTestNow(now()->subDays(30));
        InventoryMovement::query()->create([
            'occurred_at' => now(),
            'custom_unique_reference_id' => 'test',
            'type' => 'sale',
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity_before' => 0,
            'quantity_delta' => $randomQuantity * -1,
            'quantity_after' => $inventory->quantity + $randomQuantity * -1,
            'unit_cost' => $inventory->prices->cost,
            'unit_price' => $inventory->prices->price,
            'description' => 'test',
        ]);

        Carbon::setTestNow(now());

        /** @var InventoryMovementsStatistic $inventoryMovementStatistic */
        $inventoryMovementStatistic = InventoryMovementsStatistic::query()
            ->where('inventory_id', $inventory->id)
            ->first();

        ray($inventoryMovementStatistic);

        $this->assertNotNull($inventoryMovementStatistic);

        ray()->showQueries();
        RemoveOutdatedSalesJob::dispatchSync();
        RemoveOutdatedSalesJob::dispatchSync();

        ray(InventoryMovementsStatistic::query()->get()->toArray());
        ray(InventoryMovement::query()->get()->toArray());
        $inventoryMovementStatistic->refresh();

        $this->assertEquals(0, $inventoryMovementStatistic->last7days_quantity_delta, '7 days');
        $this->assertEquals($randomQuantity, $inventoryMovementStatistic->last14days_quantity_delta, '14 days');
        $this->assertEquals($randomQuantity, $inventoryMovementStatistic->last28days_quantity_delta, '28 days');

        // we just make sure jobs run without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function test_if_sales_are_added_to_statistics(): void
    {
        InventoryMovementsStatisticsServiceProvider::enableModule();

        Product::factory()->create();
        Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::first();

        $quantitySold = rand(1, 100);
        InventoryService::sell($inventory, $quantitySold);

        /** @var InventoryMovementsStatistic $inventoryMovementStatistic */
        $inventoryMovementStatistic = InventoryMovementsStatistic::query()
            ->where('inventory_id', $inventory->id)
            ->first();

        $this->assertNotNull($inventoryMovementStatistic);

        $this->assertEquals($quantitySold, $inventoryMovementStatistic->last7days_quantity_delta);
        $this->assertEquals($quantitySold, $inventoryMovementStatistic->last14days_quantity_delta);
        $this->assertEquals($quantitySold, $inventoryMovementStatistic->last28days_quantity_delta);

        // we just make sure jobs run without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function test_module_basic_functionality(): void
    {
        InventoryMovementsStatisticsServiceProvider::enableModule();

        Product::factory()->create();
        Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::first();

        $randomQuantity = rand(1, 100);

        InventoryMovement::query()->create([
            'occurred_at' => now(),
            'custom_unique_reference_id' => 'test',
            'type' => 'sale',
            'inventory_id' => $inventory->id,
            'product_id' => $inventory->product_id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity_before' => $inventory->quantity + $randomQuantity * -1,
            'quantity_delta' => $randomQuantity * -1,
            'quantity_after' => $inventory->quantity + $randomQuantity * -1,
            'unit_cost' => $inventory->prices->cost,
            'unit_price' => $inventory->prices->price,
            'description' => 'test',
        ]);

        RemoveOutdatedSalesJob::dispatch();

        // we just make sure jobs run without errors
        $this->assertTrue(true);
    }
}

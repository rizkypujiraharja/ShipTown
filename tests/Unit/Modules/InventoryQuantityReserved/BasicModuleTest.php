<?php

namespace Tests\Unit\Modules\InventoryQuantityReserved;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\Inventory\src\Jobs\DispatchRecalculateInventoryRecordsJob;
use App\Modules\InventoryQuantityReserved\src\InventoryQuantityReservedServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryQuantityReservedServiceProvider::enableModule();
    }

    /** @test */

    /** @test */
    public function correctQtyReservedAfterRecalculateInventoryRecordsJobTest(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        Configuration::updateOrCreate([], ['warehouse_id' => $warehouse->getKey()]);

        $inventory = Inventory::find($product->getKey(), $warehouse->getKey());

        InventoryReservation::create([
            'inventory_id' => $inventory->id,
            'product_sku' => $product->sku,
            'warehouse_code' => $inventory->warehouse_code,
            'quantity_reserved' => 10,
            'comment' => 'test',
            'custom_uuid' => 'test',
        ]);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 10,
        ]);

        // update the quantity_reserved field in the inventory table so that
        // we know that the total is wrong and set recount_required to 1
        $inventory->quantity_reserved = 5;
        $inventory->recount_required = true;
        $inventory->save();

        // run the job to recalculate the inventory record back to the correct total
        DispatchRecalculateInventoryRecordsJob::dispatchSync();

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'quantity_reserved' => 10,
        ]);
    }

    /** @test */
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

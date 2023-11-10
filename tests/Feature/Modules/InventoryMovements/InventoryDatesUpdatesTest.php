<?php

namespace Tests\Feature\Modules\InventoryMovements;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\InventoryLastMovementIdJob;
use App\Modules\InventoryMovements\src\Jobs\PreviousMovementIdJob;
use App\Services\InventoryService;
use Tests\TestCase;

class InventoryDatesUpdatesTest extends TestCase
{
    private InventoryMovement $inventoryMovement01;
    private InventoryMovement $inventoryMovement02;
    private float $initialQuantity;
    private Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsServiceProvider::enableModule();

        /** @var Product $product */
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory = Inventory::find($product->getKey(), $warehouse->getKey());

//        $this->initialQuantity = $this->inventory->quantity;

//        $this->inventoryMovement01 = InventoryService::adjustQuantity($this->inventory, 20, '');
//        $this->inventoryMovement02 = InventoryService::sellProduct($this->inventory, -5, '');

        PreviousMovementIdJob::dispatch();
        InventoryLastMovementIdJob::dispatch();
    }

    /** @test */
    public function testTransferInType()
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_TRANSFER_IN,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => $this->inventory->quantity,
            'quantity_delta' => 10,
            'quantity_after' => $this->inventory->quantity + 10,
            'description' => 'test',
        ]);

        $this->inventory = $this->inventory->refresh();

        $this->assertEquals($movement->getKey(), $this->inventory->last_movement_id, 'last_movement_id');
        $this->assertEquals($movement->occurred_at, $this->inventory->first_movement_at, 'first_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_movement_at, 'last_movement_at');
    }

    /** @test */
    public function testStocktakeType()
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_STOCKTAKE,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => 5,
            'quantity_delta' => 45,
            'quantity_after' => 50,
            'description' => 'test',
        ]);

        $this->inventory = $this->inventory->refresh();

        $this->assertEquals($movement->quantity_after, $this->inventory->quantity, 'last_movement_id');
        $this->assertEquals($movement->getKey(), $this->inventory->last_movement_id, 'last_movement_id');
        $this->assertEquals($movement->occurred_at, $this->inventory->first_movement_at, 'first_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_movement_at, 'last_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_counted_at, 'last_movement_at');
    }

    /** @test */
    public function testSaleType()
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_SALE,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => 10,
            'quantity_delta' => -2,
            'quantity_after' => 8,
            'description' => 'test',
        ]);

        $this->inventory = $this->inventory->refresh();

        $this->assertEquals($movement->quantity_after, $this->inventory->quantity, 'last_movement_id');
        $this->assertEquals($movement->getKey(), $this->inventory->last_movement_id, 'last_movement_id');
        $this->assertEquals($movement->occurred_at, $this->inventory->first_movement_at, 'first_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_movement_at, 'last_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->first_sold_at, 'last_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_sold_at, 'last_movement_at');
    }
}
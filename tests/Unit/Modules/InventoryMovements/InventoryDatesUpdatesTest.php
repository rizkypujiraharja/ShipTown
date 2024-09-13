<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use Tests\TestCase;

class InventoryDatesUpdatesTest extends TestCase
{
    private Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsServiceProvider::enableModule();

        /** @var Product $product */
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory = Inventory::find($product->getKey(), $warehouse->getKey());
    }

    /** @test */
    public function testTransferInType(): void
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_TRANSFER_IN,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_code' => $this->inventory->warehouse_code,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => $this->inventory->quantity,
            'quantity_delta' => 10,
            'quantity_after' => $this->inventory->quantity + 10,
            'unit_cost' => $this->inventory->prices->cost,
            'unit_price' => $this->inventory->prices->price,
            'description' => 'test',
        ]);

        $this->inventory = $this->inventory->refresh();

        $this->assertEquals($movement->getKey(), $this->inventory->last_movement_id, 'last_movement_id');
        $this->assertEquals($movement->occurred_at, $this->inventory->first_movement_at, 'first_movement_at');
        $this->assertEquals($movement->occurred_at, $this->inventory->last_movement_at, 'last_movement_at');
    }

    /** @test */
    public function testStocktakeType(): void
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_STOCKTAKE,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_code' => $this->inventory->warehouse_code,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => 5,
            'quantity_delta' => 45,
            'quantity_after' => 50,
            'unit_cost' => $this->inventory->prices->cost,
            'unit_price' => $this->inventory->prices->price,
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
    public function testSaleType(): void
    {
        /** @var InventoryMovement $movement */
        $movement = InventoryMovement::query()->create([
            'occurred_at' => now()->toDateTimeString(),
            'type' => InventoryMovement::TYPE_SALE,
            'inventory_id' => $this->inventory->getKey(),
            'product_id' => $this->inventory->product_id,
            'warehouse_code' => $this->inventory->warehouse_code,
            'warehouse_id' => $this->inventory->warehouse_id,
            'quantity_before' => 10,
            'quantity_delta' => -2,
            'quantity_after' => 8,
            'unit_cost' => $this->inventory->prices->cost,
            'unit_price' => $this->inventory->prices->price,
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

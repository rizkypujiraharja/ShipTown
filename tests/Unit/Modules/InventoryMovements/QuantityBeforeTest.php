<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\QuantityBeforeCheckJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Services\InventoryService;
use Tests\TestCase;

class QuantityBeforeTest extends TestCase
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

    public function testIncorrectStockTakeQuantityBefore(): void
    {
        ray()->showQueries();

        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::adjust($this->inventory, 10);
        $stocktakeMovement = InventoryService::stocktake($this->inventory, 5);

        $stocktakeMovement->update([
            'quantity_before' => 0,
        ]);

        QuantityBeforeCheckJob::dispatch();

        SequenceNumberJob::dispatch();

        ray(InventoryMovement::query()->get()->toArray());

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $stocktakeMovement->refresh();

        $this->assertEquals($inventoryMovement02->quantity_before, $inventoryMovement01->quantity_after);
        $this->assertEquals($stocktakeMovement->quantity_before, $inventoryMovement02->quantity_after);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $inventoryMovement02->getKey(),
            'quantity_before' => $inventoryMovement01->quantity_after,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $stocktakeMovement->getKey(),
            'quantity_before' => $inventoryMovement02->quantity_after,
        ]);
    }

    public function testIncorrectQuantityBefore(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $inventoryMovement03 = InventoryService::adjust($this->inventory, -7);

        $inventoryMovement02->update([
            'quantity_before' => 0,
        ]);

        QuantityBeforeCheckJob::dispatch();
        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals($inventoryMovement02->quantity_before, $inventoryMovement01->quantity_after);
        $this->assertEquals($inventoryMovement03->quantity_before, $inventoryMovement02->quantity_after);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $inventoryMovement02->getKey(),
            'quantity_before' => $inventoryMovement01->quantity_after,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $inventoryMovement03->getKey(),
            'quantity_before' => $inventoryMovement02->quantity_after,
        ]);
    }

    public function testBasicScenario(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $inventoryMovement03 = InventoryService::adjust($this->inventory, 7);

        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals($inventoryMovement02->quantity_before, $inventoryMovement01->quantity_after);
        $this->assertEquals($inventoryMovement03->quantity_before, $inventoryMovement02->quantity_after);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $inventoryMovement02->getKey(),
            'quantity_before' => $inventoryMovement01->quantity_after,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $inventoryMovement03->getKey(),
            'quantity_before' => $inventoryMovement02->quantity_after,
        ]);
    }
}

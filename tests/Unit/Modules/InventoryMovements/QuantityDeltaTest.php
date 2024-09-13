<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\QuantityDeltaCheckJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Services\InventoryService;
use Tests\TestCase;

class QuantityDeltaTest extends TestCase
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

    public function testIncorrectQuantityDeltaScenario(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $stocktakeMovement = InventoryService::stocktake($this->inventory, 7);

        $inventoryMovement01->update(['quantity_delta' => $inventoryMovement01->quantity_delta + 10]);
        $inventoryMovement02->update(['quantity_delta' => $inventoryMovement02->quantity_delta + 10]);
        $stocktakeMovement->update(['quantity_delta' => $stocktakeMovement->quantity_delta + 10]);

        QuantityDeltaCheckJob::dispatch();
        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $stocktakeMovement->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals($inventoryMovement01->quantity_delta, $inventoryMovement01->quantity_after - $inventoryMovement01->quantity_before, 'Movement01');
        $this->assertEquals($inventoryMovement02->quantity_delta, $inventoryMovement02->quantity_after - $inventoryMovement02->quantity_before, 'Movement02');
        $this->assertEquals($stocktakeMovement->quantity_delta, $stocktakeMovement->quantity_after - $stocktakeMovement->quantity_before, 'Movement03');
    }

    public function testBasicScenario(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $inventoryMovement03 = InventoryService::stocktake($this->inventory, 7);

        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals($inventoryMovement01->quantity_delta, $inventoryMovement01->quantity_after - $inventoryMovement01->quantity_before, 'Movement01');
        $this->assertEquals($inventoryMovement02->quantity_delta, $inventoryMovement02->quantity_after - $inventoryMovement02->quantity_before, 'Movement02');
        $this->assertEquals($inventoryMovement03->quantity_delta, $inventoryMovement03->quantity_after - $inventoryMovement03->quantity_before, 'Movement03');
    }
}

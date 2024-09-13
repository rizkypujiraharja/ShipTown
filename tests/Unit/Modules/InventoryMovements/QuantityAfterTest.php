<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\QuantityAfterCheckJob;
use App\Services\InventoryService;
use Tests\TestCase;

class QuantityAfterTest extends TestCase
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

    public function testThatDoesntChangeStocktakeQuantityAfter(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $stocktakeMovement = InventoryService::stocktake($this->inventory, 7);

        $quantityAfterOriginal = $stocktakeMovement->quantity_after;

        $inventoryMovement01->update(['quantity_delta' => $inventoryMovement01->quantity_delta + 10]);
        $inventoryMovement02->update(['quantity_delta' => $inventoryMovement02->quantity_delta + 10]);
        $stocktakeMovement->update(['quantity_delta' => $stocktakeMovement->quantity_delta + 10]);

        ray(InventoryMovement::query()->get()->toArray());

        QuantityAfterCheckJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $stocktakeMovement->refresh();

        $this->assertEquals($inventoryMovement01->quantity_after, $inventoryMovement01->quantity_before + $inventoryMovement01->quantity_delta, 'Movement01');
        $this->assertEquals($inventoryMovement02->quantity_after, $inventoryMovement02->quantity_before + $inventoryMovement02->quantity_delta, 'Movement02');
        $this->assertEquals($stocktakeMovement->quantity_after, $quantityAfterOriginal, 'Movement03');
    }

    public function testIncorrectQuantityAfter(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::sell($this->inventory, -5);
        $inventoryMovement03 = InventoryService::stocktake($this->inventory, 7);

        $inventoryMovement02->update([
            'quantity_after' => $inventoryMovement02->quantity_after + 1,
        ]);

        ray(InventoryMovement::query()->get()->toArray());

        ray()->showQueries();

        QuantityAfterCheckJob::dispatch();

        ray()->stopShowingQueries();

        ray(InventoryMovement::query()->get()->toArray());

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();

        $this->assertEquals($inventoryMovement01->quantity_after, $inventoryMovement01->quantity_before + $inventoryMovement01->quantity_delta, 'Movement01');
        $this->assertEquals($inventoryMovement02->quantity_after, $inventoryMovement02->quantity_before + $inventoryMovement02->quantity_delta, 'Movement02');
        $this->assertEquals(7, $inventoryMovement03->quantity_after, 'Movement03');
    }

    public function testBasicScenario(): void
    {
        $inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $inventoryMovement02 = InventoryService::stocktake($this->inventory, 5);
        $inventoryMovement03 = InventoryService::sell($this->inventory, -4);

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals($inventoryMovement01->quantity_after, $inventoryMovement01->quantity_before + $inventoryMovement01->quantity_delta, 'Movement01');
        $this->assertEquals($inventoryMovement02->quantity_after, 5, 'Movement02');
        $this->assertEquals($inventoryMovement03->quantity_after, $inventoryMovement03->quantity_before + $inventoryMovement03->quantity_delta, 'Movement03');
    }
}

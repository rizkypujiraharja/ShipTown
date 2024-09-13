<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Services\InventoryService;
use Tests\TestCase;

class SequenceNumberJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsServiceProvider::enableModule();
    }

    public function testBasicScenario(): void
    {
        $warehouse = Warehouse::factory()->create();

        $inventory = Product::factory()->create()->inventory()->first();
        $inventory2 = Product::factory()->create()->inventory()->first();

        $inventoryMovement01 = InventoryService::adjust($inventory, 20);
        $inventoryMovement02 = InventoryService::sell($inventory, -5);
        $inventoryMovement03 = InventoryService::stocktake($inventory, 7);

        ray(InventoryMovement::query()->get()->toArray());

        ray()->showQueries();
        SequenceNumberJob::dispatch();

        $inventoryMovement04 = InventoryService::sell($inventory, -7);
        $inventoryMovement05 = InventoryService::sell($inventory2, -7);

        SequenceNumberJob::dispatch();

        $inventoryMovement01->refresh();
        $inventoryMovement02->refresh();
        $inventoryMovement03->refresh();
        $inventoryMovement04->refresh();
        $inventoryMovement05->refresh();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertEquals(1, $inventoryMovement01->sequence_number);
        $this->assertEquals(2, $inventoryMovement02->sequence_number);
        $this->assertEquals(3, $inventoryMovement03->sequence_number);
        $this->assertEquals(4, $inventoryMovement04->sequence_number);

        $this->assertEquals(1, $inventoryMovement05->sequence_number);
    }
}

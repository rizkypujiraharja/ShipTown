<?php

namespace Tests\Unit\Modules\InventoryMovements;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Inventory\src\Jobs\DispatchRecalculateInventoryRecordsJob;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovements\src\Jobs\InventoryQuantityCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityAfterCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityBeforeCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityDeltaCheckJob;
use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;
use App\Services\InventoryService;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    private InventoryMovement $inventoryMovement01;

    private InventoryMovement $inventoryMovement02;

    private Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        InventoryMovementsServiceProvider::enableModule();

        /** @var Product $product */
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->inventory = Inventory::find($product->getKey(), $warehouse->getKey());

        $this->inventoryMovement01 = InventoryService::adjust($this->inventory, 20);
        $this->inventoryMovement02 = InventoryService::sell($this->inventory, -5);

        SequenceNumberJob::dispatch();
    }

    /** @test */
    public function testInventoryQuantityJob(): void
    {
        $this->inventory->update([
            'recount_required' => true,
            'quantity' => 0,
        ]);

        InventoryQuantityCheckJob::dispatch();

        SequenceNumberJob::dispatch();

        DispatchRecalculateInventoryRecordsJob::dispatch();

        ray(InventoryMovement::query()->get()->toArray());

        $this->assertDatabaseHas('inventory', [
            'id' => $this->inventory->getKey(),
            'last_movement_id' => $this->inventoryMovement02->getKey(),
            'quantity' => $this->inventoryMovement02->quantity_after,
        ]);
    }

    /** @test */
    public function testEmptyDatabaseRun(): void
    {
        QuantityBeforeCheckJob::dispatch();
        QuantityDeltaCheckJob::dispatch();
        QuantityAfterCheckJob::dispatch();
        InventoryQuantityCheckJob::dispatch();

        $this->assertTrue(true, 'We did not run into any errors');
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

    /** @test */
    public function testPreviousMovementIdJob(): void
    {
        $this->inventoryMovement02->update([
            'quantity_before' => 100,
        ]);

        QuantityBeforeCheckJob::dispatch();

        SequenceNumberJob::dispatch();

        $this->inventoryMovement01->refresh();
        $this->inventoryMovement02->refresh();

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $this->inventoryMovement01->getKey(),
            'sequence_number' => 1,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $this->inventoryMovement02->getKey(),
            'sequence_number' => 2,
        ]);
    }

    /** @test */
    public function testQuantityDeltaAndAfterJob(): void
    {
        $inventoryMovement03 = InventoryService::adjust($this->inventory, 10);
        QuantityDeltaCheckJob::dispatch();
        QuantityAfterCheckJob::dispatch();

        SequenceNumberJob::dispatch();

        $this->assertDatabaseHas('inventory_movements', [
            'id' => $this->inventoryMovement02->getKey(),
            'sequence_number' => 2,
            'quantity_before' => 20,
            'quantity_delta' => -5,
            'quantity_after' => 15,
        ]);
    }

    /** @test */
    public function testLastMovementIdJob(): void
    {
        Inventory::query()->update([
            'recount_required' => true,
            'last_movement_id' => null,
            'last_movement_at' => null,
            'quantity' => 0,
        ]);

        InventoryQuantityCheckJob::dispatch();
        SequenceNumberJob::dispatch();
        DispatchRecalculateInventoryRecordsJob::dispatch();

        $this->assertDatabaseHas('inventory', [
            'id' => $this->inventory->id,
            'last_movement_id' => $this->inventoryMovement02->id,
            'last_movement_at' => $this->inventoryMovement02->occurred_at,
            'quantity' => $this->inventoryMovement02->quantity_after,
        ]);
    }
}

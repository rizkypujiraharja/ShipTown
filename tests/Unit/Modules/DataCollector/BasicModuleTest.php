<?php

namespace Tests\Unit\Modules\DataCollector;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function testTransferInScannedAction(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])
            ->first();

        ray($inventory->toArray());

        $quantityBefore = $inventory->quantity;

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create(['warehouse_id' => $inventory->warehouse_id]);

        /** @var DataCollectionRecord $record */
        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'inventory_id' => $inventory->getKey(),
            'product_id' => $inventory->product_id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
        ]);

        DataCollectorService::runAction($dataCollection, 'transfer_in_scanned');

        $dataCollection->refresh();
        $inventory->refresh();
        $record->refresh();

        ray($quantityBefore, $dataCollection->toArray(), $inventory->toArray(), $record->toArray());

        $this->assertNull($dataCollection->currently_running_task);
        $this->assertEmpty(DataCollectionRecord::query()->where('quantity_scanned', '!=', 0)->get());
        $this->assertEquals($inventory->quantity, $quantityBefore + $record->total_transferred_in);
        $this->assertEquals(0, $record->total_transferred_out);
    }

    public function testTransferOutScannedAction(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])
            ->first();

        $quantityBefore = $inventory->quantity;

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create(['warehouse_id' => $inventory->warehouse_id]);

        /** @var DataCollectionRecord $record */
        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'inventory_id' => $inventory->getKey(),
            'product_id' => $inventory->product_id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
        ]);

        DataCollectorService::runAction($dataCollection, 'transfer_out_scanned');

        $dataCollection->refresh();

        $this->assertNull($dataCollection->currently_running_task);
        $this->assertEmpty(DataCollectionRecord::query()->where('quantity_scanned', '!=', 0)->get());
        $this->assertEquals(0, $record->total_transferred_in);
        $this->assertEquals($inventory->quantity, $quantityBefore - $record->total_transferred_out);
    }

    public function testTransferToScannedAction(): void
    {
        $product = Product::factory()->create();
        $sourceWarehouse = Warehouse::factory()->create();
        /** @var Warehouse $destinationWarehouse */
        $destinationWarehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()->where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $sourceWarehouse->getKey(),
        ])
            ->first();

        $quantityBefore = $inventory->quantity;

        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $inventory->warehouse_id,
            'destination_warehouse_id' => $destinationWarehouse->getKey(),
        ]);

        /** @var DataCollectionRecord $record */
        $record = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'inventory_id' => $inventory->getKey(),
            'product_id' => $inventory->product_id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
        ]);

        DataCollectorService::runAction($dataCollection, 'transfer_to_scanned');

        $dataCollection->refresh();

        $this->assertDatabaseHas('data_collections', [
            'warehouse_id' => $destinationWarehouse->getKey(),
        ]);
        $this->assertDatabaseHas('data_collection_records', [
            'warehouse_id' => $destinationWarehouse->getKey(),
            'product_id' => $product->getKey(),
        ]);
        $this->assertNull($dataCollection->currently_running_task);
        $this->assertEmpty(DataCollectionRecord::query()->where('quantity_scanned', '!=', 0)->get());
        $this->assertEquals(0, $record->total_transferred_in);
        $this->assertEquals($inventory->quantity, $quantityBefore - $record->total_transferred_out);
    }
}

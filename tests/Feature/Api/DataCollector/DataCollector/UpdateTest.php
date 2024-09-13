<?php

namespace Tests\Feature\Api\DataCollector\DataCollector;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_transfer_in_scanned_action_call_returns_ok(): void
    {
        $randomQuantity = 10;

        $user = User::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::query()->firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $dataCollector */
        $dataCollector = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Test',
        ]);

        /** @var DataCollectionRecord $dataCollectorRecord */
        $dataCollectorRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollector->id,
            'inventory_id' => $inventory->id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => $randomQuantity,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('api.data-collector.update', [
            'data_collector' => $dataCollector->getKey(),
        ]), [
            'action' => 'transfer_in_scanned',
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertEquals($randomQuantity, $inventory->refresh()->quantity);

        $this->assertEquals(0, $dataCollectorRecord->refresh()->quantity_scanned);

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    /** @test */
    public function test_transfer_out_scanned_action_call_returns_ok(): void
    {
        $randomQuantity = 10;

        $user = User::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Inventory $inventory */
        $inventory = Inventory::firstOrCreate([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ], []);

        /** @var DataCollection $dataCollector */
        $dataCollector = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'name' => 'Test',
        ]);

        /** @var DataCollectionRecord $dataCollectorRecord */
        $dataCollectorRecord = DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollector->id,
            'inventory_id' => $inventory->id,
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => $randomQuantity,
        ]);

        $response = $this->actingAs($user, 'api')->putJson(route('api.data-collector.update', [
            'data_collector' => $dataCollector->getKey(),
        ]), [
            'action' => 'transfer_out_scanned',
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertEquals($randomQuantity * -1, $inventory->refresh()->quantity);

        $this->assertEquals(0, $dataCollectorRecord->refresh()->quantity_scanned);

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

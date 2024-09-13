<?php

namespace Tests\Feature\Api\DataCollectorRecords;

use App\Models\DataCollection;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        Warehouse::factory()->create();
        $user = User::factory()->create();

        $inventory = Product::factory()->create()->inventory()->first();

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $inventory->warehouse_id,
            'warehouse_code' => $inventory->warehouse_code,
            'name' => 'test',
        ]);

        $response = $this->actingAs($user, 'api')->postJson(route('data-collector-records.store'), [
            'data_collection_id' => $dataCollection->getKey(),
            'inventory_id' => $inventory->getKey(),
            'warehouse_code' => $inventory->warehouse_code,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $inventory->product_id,
            'quantity_scanned' => rand(0, 100),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'product_id',
                'quantity_requested',
                'quantity_to_scan',
                'quantity_scanned',
            ],
        ]);
    }
}

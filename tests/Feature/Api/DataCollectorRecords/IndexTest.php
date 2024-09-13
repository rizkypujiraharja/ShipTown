<?php

namespace Tests\Feature\Api\DataCollectorRecords;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => Warehouse::factory()->create()->getKey(),
            'name' => 'test',
        ]);

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => Product::factory()->create()->getKey(),
            'warehouse_id' => $dataCollection->warehouse_id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'quantity_scanned' => rand(1, 10),
        ]);

        $response = $this->actingAs($user, 'api')->getJson(route('data-collector-records.index'));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}

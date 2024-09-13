<?php

namespace Tests\Feature\Api\DataCollectorActions\ImportAsStocktake;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/data-collector-actions/import-as-stocktake';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('user');

        $dataCollection = DataCollection::factory()->create();

        DataCollectionRecord::factory()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'warehouse_id' => $dataCollection->warehouse_id,
            'warehouse_code' => $dataCollection->warehouse_code,
        ]);

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'data_collection_id' => $dataCollection->getKey(),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'data_collection_id',
            ],
        ]);
    }
}

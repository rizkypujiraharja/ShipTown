<?php

namespace Tests\Feature\Api\CsvImport;

use App\Models\DataCollection;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $dataCollection = DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'name' => 'test',
        ]);

        /** @var Product $product */
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson(route('api.csv-import.store'), [
            'data_collection_id' => $dataCollection->getKey(),
            'data' => [
                [
                    'product_sku' => $product->sku,
                    'quantity_requested' => rand(0, 100),
                    'quantity_scanned' => rand(0, 100),
                ],
            ],
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'success',
            ],
        ]);
    }
}

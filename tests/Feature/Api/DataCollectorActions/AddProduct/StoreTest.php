<?php

namespace Tests\Feature\Api\DataCollectorActions\AddProduct;

use App\Models\DataCollection;
use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/data-collector-actions/add-product';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('user');
        $dataCollection = DataCollection::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'data_collection_id' => $dataCollection->id,
            'sku_or_alias' => $product->sku,
            'quantity_scanned' => -50 + rand(1, 100),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}

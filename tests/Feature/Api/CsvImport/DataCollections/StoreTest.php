<?php

namespace Tests\Feature\Api\CsvImport\DataCollections;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson(route('csv-import-data-collections.store'), [
            'data_collection_name_prefix' => 'test',
            'data' => [
                [
                    'product_sku' => $product->sku,
                    $warehouse->code => rand(0, 100),
                ],
            ],
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'success',
            ],
        ]);
    }
}

<?php

namespace Tests\Feature\Api\Stocktakes;

use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.stocktakes.store'), [
                'warehouse_id' => $warehouse->getKey(),
                'product_id' => $product->getKey(),
                'new_quantity' => 0,
            ]);

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'inventory_id',
                    'product_id',
                    'warehouse_id',
                    'quantity_delta',
                    'quantity_before',
                    'quantity_after',
                    'description',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }
}

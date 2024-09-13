<?php

namespace Tests\Feature\Api\Inventory;

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

        Warehouse::factory()->create();
        Product::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('api/inventory?include=product');

        $response->assertOk();

        $this->assertGreaterThan(0, $response->json('meta.total'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'product_id',
                    'warehouse_id',
                    'quantity_available',
                    'quantity',
                    'quantity_reserved',
                    'restock_level',
                    'reorder_point',
                    'quantity_required',
                    'product' => [],
                ],
            ],
        ]);
    }
}

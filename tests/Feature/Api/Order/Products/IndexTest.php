<?php

namespace Tests\Feature\Api\Order\Products;

use App\Models\OrderProduct;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        OrderProduct::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('order.products.index', [
            'include' => [
                'order',
                'product',
                'product.aliases',
            ],
        ]));

        $response->assertOk();

        $this->assertNotEquals(0, $response->json('meta.total'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    'order_id',
                    'product_id',
                    'sku_ordered',
                    'name_ordered',
                    'quantity_ordered',
                    'quantity_picked',
                    'quantity_shipped',
                    'order' => [],
                    'product' => [
                        'aliases' => [],
                    ],
                ],
            ],
        ]);
    }
}

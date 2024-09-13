<?php

namespace Tests\Feature\Api\Order\Products\Product;

use App\Models\OrderProduct;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_update_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $orderProduct = OrderProduct::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson(route('order.products.update', [$orderProduct]), [
            'quantity_shipped' => $orderProduct->quantity_to_ship,
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'order_id',
                'product_id',
                'sku_ordered',
                'name_ordered',
                'quantity_ordered',
                'quantity_picked',
                'quantity_shipped',
            ],
        ]);
    }
}

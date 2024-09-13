<?php

namespace Tests\Feature\Api\Orders\Products\Shipments;

use App\Models\Order;
use App\Models\OrderProduct;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('user');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $response = $this->postJson('/api/orders/products/shipments', [
            'product_id' => $orderProduct->product_id,
            'order_id' => $orderProduct->order_id,
            'order_product_id' => $orderProduct->getKey(),
            'quantity_shipped' => $orderProduct->quantity_to_ship,
        ]);

        $response->assertSuccessful();
    }
}

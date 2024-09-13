<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Order;
use App\Models\OrderProduct;
use Tests\TestCase;

class ProductLinesCountTest extends TestCase
{
    public function testIfProductLineCountIsPopulatedCorrectly(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();

        $orderProductCount = rand(1, 5);

        $order->orderProducts()->saveMany(
            OrderProduct::factory()->count($orderProductCount)->make()
        );

        $order = $order->refresh();

        $this->assertEquals($order->orderProductsTotals->count, $orderProductCount);
    }
}

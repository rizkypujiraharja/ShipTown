<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductTotal;
use App\Modules\Automations\src\Conditions\Order\IsFullyPaidCondition;
use Tests\TestCase;

class IsFullyPaidConditionTest extends TestCase
{
    public function test_if_order_without_products_is_considered_unpaid(): void
    {
        Order::query()->forceDelete();

        /** @var Order $order */
        $order = Order::factory()->create(['total_shipping' => 10, 'total_paid' => 20]);
        $order->refresh();

        ray($order->toArray());

        $query = Order::query();
        IsFullyPaidCondition::addQueryScope($query, 'True');
        $this->assertCount(0, $query->get(), 'Order should be considered as not paid');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());
        ray($query->toSql(), $query->getBindings());
        ray($query->get()->toArray());
    }

    public function test_partially_paid(): void
    {
        Order::query()->forceDelete();

        /** @var Order $order */
        $order = Order::factory()->create(['total_shipping' => 10]);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        Order::query()->where(['id' => $order->getKey()])->update(['total_paid' => $order->total_order / 2]);

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'false');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());
        ray($query->toSql());

        $this->assertCount(1, $query->get(), 'Order has not been returned as unpaid');
    }

    public function test_order_not_paid(): void
    {
        Order::query()->forceDelete();

        /** @var Order $order3 */
        $order3 = Order::factory()->create();
        OrderProduct::factory()->create(['order_id' => $order3->getKey()]);

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'false');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());

        ray($query->toSql());

        $this->assertCount(1, $query->get(), 'Order has not been returned as unpaid');
    }

    public function test_order_0_total_0_paid(): void
    {
        Order::query()->forceDelete();

        // scenario 4: order4 is not paid but also has 0 total, should be considered as NOT PAID
        /** @var Order $order4 */
        $order4 = Order::factory()->create(['total_shipping' => 0]);
        OrderProduct::factory()->create(['order_id' => $order4->getKey(), 'price' => 0]);

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'true');

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());

        ray($query->toSql());

        $this->assertCount(1, $query->get(), 'Order has not been returned as paid');
    }

    public function test_order_0_total_but_paid(): void
    {
        Order::query()->forceDelete();

        // scenario 5: order is paid but 0 total, it should be considered as PAID
        /** @var Order $order3 */
        $order = Order::factory()->create();
        OrderProduct::factory()->create(['order_id' => $order->getKey(), 'price' => 0]);
        $order->update(['total_paid' => 100]);

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'true');

        ray($order->toArray());
        ray($query->get()->toArray());

        $this->assertEquals(1, $query->count(), 'Order has not been returned as paid');
    }

    public function test_order_fully_paid(): void
    {
        Order::query()->forceDelete();

        /** @var Order $order */
        $order = Order::factory()->create();
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);
        $order = $order->refresh();
        $order->update(['total_paid' => $order->total_order]);

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'True');

        ray($order->toArray());
        ray($query->get()->toArray());

        $this->assertEquals(1, $query->count(), 'Order has not been returned as paid');
    }

    public function test_order_paid_with_discounts(): void
    {
        Order::query()->forceDelete();

        /** @var Order $order */
        $order = Order::factory()->create();
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);
        $order->refresh();

        $query = Order::query();

        IsFullyPaidCondition::addQueryScope($query, 'true');

        Order::query()->where(['id' => $order->getKey()])->update(['total_discounts' => $order->total_products + $order->total_shipping]);

        ray($order->refresh()->toArray());
        ray($query->toSql());
        ray($query->get()->toArray());

        $this->assertEquals(1, $query->count(), 'Order has not been returned as paid');
    }
}

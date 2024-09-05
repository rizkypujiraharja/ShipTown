<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductTotal;
use App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition;
use Tests\TestCase;

class IsFullyPackedConditionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $order3 = Order::factory()->create();

        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = OrderProduct::factory()->create(['order_id' => $order1->getKey()]);

        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = OrderProduct::factory()->create(['order_id' => $order2->getKey()]);

        /** @var OrderProduct $orderProduct3 */
        $orderProduct3 = OrderProduct::factory()->create(['order_id' => $order3->getKey()]);

        $orderProduct1->update(['quantity_shipped' => $orderProduct1->quantity_ordered]);
    }

    public function test_condition_query_scope()
    {
        $query = Order::query();

        IsFullyPackedCondition::addQueryScope($query, 'true');

        $this->assertEquals(1, $query->count());
    }

    public function test_condition_false_query_scope()
    {
        $query = Order::query();

        IsFullyPackedCondition::addQueryScope($query, 'false');

        ray(OrderProduct::all()->toArray());
        ray(OrderProductTotal::all()->toArray());

        ray($query->toSql());

        $this->assertEquals(2, $query->count());
    }
}

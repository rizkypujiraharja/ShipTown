<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Modules\Automations\src\Conditions\Order\IsPartiallyPaidCondition;
use Tests\TestCase;

class IsPartiallyPaidConditionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Order::factory(3)->create();
        $order = Order::first();

        $order->update(['total_paid' => $order->total_order / 2]);
    }

    public function test_condition_query_scope()
    {
        $query = Order::query();

        IsPartiallyPaidCondition::addQueryScope($query, 'true');

        $this->assertEquals(1, $query->count());
    }

    public function test_condition_false_query_scope()
    {
        $query = Order::query();

        IsPartiallyPaidCondition::addQueryScope($query, 'false');

        ray($query->toSql());

        $this->assertEquals(2, $query->count());
    }
}

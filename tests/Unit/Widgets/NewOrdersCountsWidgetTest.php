<?php

namespace Tests\Unit\Widgets;

use App\Models\Order;
use App\Widgets\NewOrdersCounts;
use Tests\TestCase;

class NewOrdersCountsWidgetTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        Order::factory()->create(['order_placed_at' => now()]);

        $widget = new NewOrdersCounts;

        $this->assertNotNull($widget->run());
    }
}

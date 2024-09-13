<?php

namespace Tests\Unit\Widgets;

use App\Models\Order;
use App\Widgets\OrdersByAgeWidget;
use Tests\TestCase;

class OrdersByAgeWidgetTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testIfRuns(): void
    {
        Order::factory()->create();

        $widget = new OrdersByAgeWidget;

        $view = $widget->run();

        $this->assertNotNull($view);
    }
}

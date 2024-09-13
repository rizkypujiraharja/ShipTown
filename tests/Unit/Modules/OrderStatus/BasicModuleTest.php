<?php

namespace Tests\Unit\Modules\OrderStatus;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Modules\OrderStatus\src\OrderStatusServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_if_order_is_active_updates(): void
    {
        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['order_active' => true]);

        Order::factory()->create(['status_code' => $orderStatus->code]);

        $this->assertDatabaseHas('orders', ['is_active' => true]);

        $orderStatus->update(['order_active' => false]);

        $this->assertDatabaseHas('orders', ['is_active' => false]);
    }

    public function test_if_order_on_hold_updates(): void
    {
        OrderStatusServiceProvider::enableModule();

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['order_on_hold' => true]);

        Order::factory()->create(['status_code' => $orderStatus->code, 'is_on_hold' => true]);

        $this->assertDatabaseHas('orders', ['is_on_hold' => true]);

        $orderStatus->update(['order_on_hold' => false]);

        $this->assertDatabaseHas('orders', ['is_on_hold' => false]);
    }

    /** @test */
    public function test_if_can_enable(): void
    {
        OrderStatusServiceProvider::enableModule();

        $this->assertTrue(true, 'Most basic test... to be continued');
    }
}

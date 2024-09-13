<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrderClosedAtTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testIfDoesNotFillClosedAtWhenCompletedStatus(): void
    {
        OrderStatus::query()->forceDelete();
        RmsapiProductImport::query()->forceDelete();
        Order::query()->forceDelete();

        OrderStatus::factory()->create([
            'code' => 'pending',
            'name' => 'pending',
            'order_active' => 1,
        ]);

        $order = Order::factory()->create([
            'status_code' => 'open',
            'order_closed_at' => null,
        ]);

        $order->update(['status_code' => 'pending']);

        $this->assertNull($order->order_closed_at);
    }

    /**
     * A basic feature test example.
     */
    public function testIfFillsClosedAtWhenCompletedStatus(): void
    {
        OrderStatus::query()->forceDelete();
        RmsapiProductImport::query()->forceDelete();
        Order::query()->forceDelete();

        OrderStatus::factory()->create([
            'code' => 'closed',
            'name' => 'closed',
            'order_active' => 0,
        ]);

        $order = Order::factory()->create([
            'status_code' => 'open',
            'order_closed_at' => null,
        ]);

        $order->update(['status_code' => 'closed']);

        $this->assertNotNull($order->order_closed_at);
    }
}

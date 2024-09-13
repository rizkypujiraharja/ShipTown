<?php

namespace Tests\Unit\Jobs;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\AutoStatusPicking\src\Jobs\RefillPickingIfEmptyJob;
use Carbon\Carbon;
use Tests\TestCase;

class RefillOldOrdersPickingJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'order_placed_at' => Carbon::now()->subDays(100),
            'status_code' => 'processing',
        ]);

        OrderProduct::factory(2)->create([
            'order_id' => $order->id,
        ]);

        $order->update(['status_code' => 'paid']);

        RefillPickingIfEmptyJob::dispatchSync();

        $this->assertDatabaseHas('orders', ['status_code' => 'picking']);
    }
}

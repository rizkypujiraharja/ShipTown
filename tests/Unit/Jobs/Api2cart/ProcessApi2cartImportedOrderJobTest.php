<?php

namespace Tests\Unit\Jobs\Api2cart;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Tests\TestCase;

class ProcessApi2cartImportedOrderJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        Order::query()->forceDelete();
        OrderProduct::query()->forceDelete();
        Api2cartOrderImports::query()->forceDelete();

        /** @var Api2cartOrderImports $importedOrder */
        $importedOrder = Api2cartOrderImports::factory()->create();

        // act
        ProcessImportedOrdersJob::dispatch($importedOrder);

        // test
        /** @var Order $order */
        $order = Order::query()->first();
        $importedOrder = $importedOrder->refresh();

        $this->assertNotNull($importedOrder->order_number, 'Order not populated on order_imports table');

        $this->assertNotNull($order, 'Order does not exist in database');
        $this->assertNotNull($order->status_code, 'Status code missing');
        $this->assertNotEquals(0, $order->total_paid, 'Order total_paid missing');
        $this->assertNotNull($order->shipping_address_id, 'Shipping address missing');
    }
}

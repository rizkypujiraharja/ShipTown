<?php

namespace Tests\Unit\Modules\Api2cart\Jobs;

use App\Models\Order;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Tests\TestCase;

class ProcessImportedRecordJobTest extends TestCase
{
    public function test(): void
    {
        Api2cartConnection::factory()->create();

        $api2cartOrderImport = Api2cartOrderImports::factory()->create();

        ProcessImportedOrdersJob::dispatch();

        $api2cartOrderImport->refresh();
        $order = Order::query()->with(['shippingAddress', 'billingAddress'])->first();

        ray($api2cartOrderImport->raw_import, $order);

        $this->assertNotEmpty($order, 'Order was not created');
        $this->assertEquals($api2cartOrderImport->order_number, $order->order_number, 'Order number does not match');
        $this->assertNotEmpty($api2cartOrderImport->when_processed, 'Order was not processed');

        // shipping address
        $this->assertEquals($order->shippingAddress->email, $api2cartOrderImport->raw_import['customer']['email']);
        $this->assertEquals($order->shippingAddress->first_name, $api2cartOrderImport->raw_import['shipping_address']['first_name']);
        $this->assertEquals($order->shippingAddress->last_name, $api2cartOrderImport->raw_import['shipping_address']['last_name']);
        $this->assertEquals($order->shippingAddress->address1, $api2cartOrderImport->raw_import['shipping_address']['address1']);
        $this->assertEquals($order->shippingAddress->address2, $api2cartOrderImport->raw_import['shipping_address']['address2']);

        // billing address
        $this->assertEquals($order->billingAddress->first_name, $api2cartOrderImport->raw_import['billing_address']['first_name']);
        $this->assertEquals($order->billingAddress->last_name, $api2cartOrderImport->raw_import['billing_address']['last_name']);
        $this->assertEquals($order->billingAddress->address1, $api2cartOrderImport->raw_import['billing_address']['address1']);
        $this->assertEquals($order->billingAddress->address2, $api2cartOrderImport->raw_import['billing_address']['address2']);
    }
}

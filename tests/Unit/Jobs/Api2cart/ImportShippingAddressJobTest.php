<?php

namespace Tests\Unit\Jobs\Api2cart;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\Api2cart\src\Jobs\ImportShippingAddressJob;
use App\Modules\Api2cart\src\Jobs\ProcessImportedOrdersJob;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use Tests\TestCase;

class ImportShippingAddressJobTest extends TestCase
{
    public function testExample(): void
    {
        Order::query()->forceDelete();
        OrderAddress::query()->forceDelete();
        Api2cartOrderImports::query()->forceDelete();

        /** @var Api2cartOrderImports $import */
        $import = Api2cartOrderImports::factory()->create();
        $import = $import->refresh();

        ProcessImportedOrdersJob::dispatch();

        $order = Order::where(['order_number' => $import->order_number])->first();
        $order->update(['shipping_address_id' => null]);

        ImportShippingAddressJob::dispatchSync($order->id);

        $order = $order->refresh();

        $this->assertNotNull($order->shipping_address_id);
    }
}

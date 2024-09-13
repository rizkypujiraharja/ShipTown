<?php

namespace Tests\Unit\Modules\AddressLabel;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Modules\AddressLabel\src\Services\AddressLabelShippingService;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();

        $shippingService = new AddressLabelShippingService;
        $shippingLabelCollection = $shippingService->ship($order->getKey());

        $this->assertCount(1, $shippingLabelCollection);

        /** @var ShippingLabel $label */
        $label = $shippingLabelCollection->first();

        $this->assertEquals('', $label->carrier);
        $this->assertEquals('address_label', $label->service);
        $this->assertEquals($order->order_number, $label->shipping_number);
        $this->assertEquals($order->id, $label->order_id);
        $this->assertNotEmpty($label->base64_pdf_labels);
    }
}

<?php

namespace Tests\Unit\Modules\DpdIreland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\ShippingLabel;
use App\Modules\DpdIreland\src\DpdIrelandServiceProvider;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use App\User;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_module_basic_functionality(): void
    {
        if (empty(env('TEST_DPD_USER'))) {
            $this->markTestSkipped('TEST_DPD_USER is not set');
        }

        DpdIreland::factory()->create([
            'token' => env('TEST_DPD_TOKEN'),
            'user' => env('TEST_DPD_USER'),
            'password' => env('TEST_DPD_PASSWORD'),
            'live' => true,
        ]);

        DpdIrelandServiceProvider::enableModule();

        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'John';
        $testAddress->last_name = 'Smith';
        $testAddress->phone = '12345678901';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'IE';
        $testAddress->postcode = 'B661BY';
        $testAddress->address1 = 'DPD Ireland';
        $testAddress->address2 = 'Unit 2B Midland Gateway Bus';
        $testAddress->city = 'Westmeath';
        $testAddress->state_code = 'Kilbeggan';
        $testAddress->email = 'john.smith@dpd.ie';
        $testAddress->save();

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $testAddress->getKey(),
            'status_code' => 'test_orders_courier_dpd_ireland',
            'label_template' => 'dpd_irl_next_day',
        ]);

        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post('api/shipping-labels', [
            'order_id' => $order->getKey(),
            'shipping_service_code' => $order->label_template,
        ]);

        ray($response->json());

        $response->assertSuccessful();

        /** @var ShippingLabel $label */
        $label = ShippingLabel::query()->first();

        $this->assertEquals('DPD Ireland', $label->carrier);
        $this->assertEquals('next_day', $label->service);
        $this->assertEquals($order->id, $label->order_id);
        $this->assertNotEmpty($label->base64_pdf_labels);
    }
}

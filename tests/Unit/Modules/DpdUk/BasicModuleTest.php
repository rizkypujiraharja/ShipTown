<?php

namespace Tests\Unit\Modules\DpdUk;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\ShippingLabel;
use App\Modules\DpdUk\src\DpdUkServiceProvider;
use App\Modules\DpdUk\src\Models\Connection;
use App\User;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_module_basic_functionality(): void
    {
        if (empty(env('TEST_DPDUK_USERNAME'))) {
            $this->markTestSkipped('TEST_DPDUK_USERNAME is not set');
        }

        $address = OrderAddress::factory()->create([
            'address1' => '58-60 Richmond Road',
            'address2' => 'Twickenham',
            'city' => 'London',
            'postcode' => 'TW1 3BE',
            'country_code' => 'GB',
            'country_name' => 'GB',
            'phone' => '12345678901',
        ]);

        Connection::factory()->create([
            'username' => env('TEST_DPDUK_USERNAME'),
            'password' => env('TEST_DPDUK_PASSWORD'),
            'account_number' => env('TEST_DPDUK_ACCNUMBER'),
            'collection_address_id' => $address->getKey(),
        ]);

        DpdUkServiceProvider::enableModule();

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $address->getKey(),
            'label_template' => 'dpd_uk_next_day',
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

        $this->assertEquals($order->id, $label->order_id);
        $this->assertNotEmpty($label->base64_pdf_labels, 'missing content');
    }
}

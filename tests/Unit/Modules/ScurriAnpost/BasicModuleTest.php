<?php

namespace Tests\Unit\Modules\ScurriAnpost;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\ScurriAnpost\src\ScurriServiceProvider;
use App\User;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    public function test_module_basic_functionality(): void
    {
        if (empty(env('SCURRI_USERNAME'))) {
            $this->markTestSkipped('SCURRI_USERNAME is not set');
        }

        ScurriServiceProvider::enableModule();

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'anpost_courier', 'label_template' => 'anpost_3day']);

        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post('api/shipping-labels', [
            'order_id' => $order->getKey(),
            'shipping_service_code' => $order->label_template,
        ]);

        ray($response->json());

        $response->assertSuccessful();
    }

    public function test_address1_too_long_scenario(): void
    {
        if (empty(env('SCURRI_USERNAME'))) {
            $this->markTestSkipped('SCURRI_USERNAME is not set');
        }

        ScurriServiceProvider::enableModule();

        $address = OrderAddress::factory()->create([
            'address1' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor, nisl eget aliquam ultricies, nisl nisl ultricies nisl, nec aliquam nisl nisl nec nisl.',
            'city' => 'Dublin',
            'postcode' => 'D02EY47',
            'country_code' => 'IE',
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $address->getKey(),
        ]);

        $this->actingAs(User::factory()->create(), 'api');

        $response = $this->post('api/shipping-labels', [
            'order_id' => $order->getKey(),
            'shipping_service_code' => 'anpost_3day',
        ]);

        ray($response->json());

        $response->assertBadRequest();
    }
}

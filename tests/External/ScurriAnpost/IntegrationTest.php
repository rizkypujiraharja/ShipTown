<?php

namespace Tests\External\ScurriAnpost;

use App\Models\Order;
use App\Models\OrderAddress;
use App\User;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    public function test_if_store_returns_ok_response(): void
    {
        $address = OrderAddress::factory()->create([
            'company' => 'TEST COMPANY',
            'address1' => 'ATHLONE BUISNESS PARK',
            'address2' => 'DUBLIN ROAD',
            'phone' => '12345678901',
            'city' => 'Dublin',
            'state_name' => 'Co. Dublin',
            'postcode' => 'N37KD81',
            'country_code' => 'IRL',
            'country_name' => 'Ireland',
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $address->getKey(),
        ]);

        $user = User::factory()->create([
            'address_label_template' => 'dpd_label',
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson('api/print/order/'.$order->order_number.'/an_post', []);

        $this->assertTrue(
            $response->getStatusCode() === 201,
            'Expected status 201, '.$response->getStatusCode().' received'
        );

        $response->assertJsonStructure([
            'data' => [
                'order_id',
                'user_id',
                'user',
                'carrier',
                'service',
                'shipping_number',
                'tracking_url',
            ],
        ]);
    }
}

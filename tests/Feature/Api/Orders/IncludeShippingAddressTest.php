<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class IncludeShippingAddressTest extends TestCase
{
    public function testIfCanIncludeShippingAddress(): void
    {
        Passport::actingAs(
            User::factory()->create()
        );

        /** @var OrderAddress $address */
        $address = OrderAddress::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $address->id,
        ]);

        $response = $this->get(
            '/api/orders?'.
            'filter[order_number]='.$order->order_number.
            '&include=shipping_address,activities',
            []
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'shipping_address',
                ],
            ],
        ]);
    }
}

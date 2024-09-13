<?php

namespace Tests\Feature\Api\Shipments;

use App\Models\Order;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create()->assignRole('user');
        $this->actingAs($user, 'api');
    }

    public function test_successful(): void
    {
        $order = Order::factory()->create();

        $response = $this->postJson(route('api.shipments.store'), [
            'order_id' => $order->getKey(),
            'shipping_number' => 'test',
            'carrier' => 'dpd_uk',
            'service' => 'overnight',
        ]);

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }

    /** @test */
    public function test_empty(): void
    {
        $response = $this->postJson(route('api.shipments.store'), []);

        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\ShippingLabels\ShippingLabel;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected string $uri = '/shipping-labels';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $order = Order::factory()->create();
        $shippingLabel = ShippingLabel::factory()->create([
            'order_id' => $order->getKey(),
            'shipping_number' => 'test',
        ]);
        $this->uri = route('shipping-labels', [$shippingLabel->getKey()]);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }

    /** @test */
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        ray($response);

        $response->assertOk();
    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri);

        $response->assertOk();
    }
}

<?php

namespace Tests\Feature\Api\ShippingLabels;

use App\Abstracts\ShippingServiceAbstract;
use App\Models\Order;
use App\Models\ShippingService;
use App\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TestShipmentService extends ShippingServiceAbstract
{
    public function ship(int $order_id): Collection
    {
        return collect();
    }
}

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        ShippingService::factory()->create([
            'code' => 'test_service',
            'service_provider_class' => TestShipmentService::class,
        ]);

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.shipping-labels.store'), [
                'shipping_service_code' => 'test_service',
                'order_id' => $order->getKey(),
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}

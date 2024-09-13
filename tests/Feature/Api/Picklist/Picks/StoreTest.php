<?php

namespace Tests\Feature\Api\Picklist\Picks;

use App\Models\Order;
use App\Models\OrderProduct;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function store_returns_an_ok_response(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($user, 'api')->postJson(route('picks.store'), [
            'product_id' => $orderProduct->product_id,
            'quantity_picked' => 1,
            'quantity_skipped_picking' => 0,
            'order_product_ids' => [$orderProduct->id],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [],
            ],
        ]);
    }
}

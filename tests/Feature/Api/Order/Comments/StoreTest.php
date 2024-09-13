<?php

namespace Tests\Feature\Api\Order\Comments;

use App\Models\Order;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $attributes = [
            'order_id' => $order->getKey(),
            'comment' => 'Test comment',
        ];

        $response = $this->actingAs($user, 'api')->postJson(route('comments.store'), $attributes);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'order_id',
                    'user_id',
                    'comment',
                ],
            ],
        ]);
    }
}

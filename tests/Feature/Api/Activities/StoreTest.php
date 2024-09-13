<?php

namespace Tests\Feature\Api\Activities;

use App\Models\Order;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $order = Order::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson(route('api.activities.store', [
                'subject_type' => 'order',
                'subject_id' => $order->getKey(),
                'description' => 'test message',
            ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

<?php

namespace Tests\Feature\Api\Orders\Order;

use App\Models\Order;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_update_call_returns_ok(): void
    {
        $order = Order::factory()->create();
        $user = User::factory()->create();

        $response = $this->putJson(route('api.orders.update', [$order]), [
            'status_code' => 'test_status_code',
            'packer_user_id' => $user->getKey(),
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                ],
            ],
        ]);
    }
}

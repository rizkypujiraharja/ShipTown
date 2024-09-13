<?php

namespace Tests\Feature\Api\OrdersStatuses;

use App\Models\OrderStatus;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        OrderStatus::factory()->create();

        $response = $this->get('api/orders-statuses');

        $response->assertSuccessful();
    }
}

<?php

namespace Tests\Feature\Api\Reports\Picks;

use App\Models\OrderProduct;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_pagination_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Warehouse::factory()->create();
        OrderProduct::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/picks?per_page=1&page=2');

        $response->assertOk();
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Warehouse::factory()->create();
        OrderProduct::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/picks');

        $response->assertOk();
    }
}

<?php

namespace Tests\Feature\Api\Modules\RmsApi\Connections;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $params = [
            'location_id' => rand(1, 99),
            'url' => 'https://demo.rmsapi.products.management',
            'username' => 'demo@products.management',
            'password' => 'secret123',
        ];

        $response = $this->post(route('api.modules.rmsapi.connections.store'), $params);

        $response->assertStatus(201);
    }
}

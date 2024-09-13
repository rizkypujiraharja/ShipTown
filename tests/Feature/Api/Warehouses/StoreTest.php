<?php

namespace Tests\Feature\Api\Warehouses;

use App\Models\Warehouse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->make();

        $data = [
            'name' => $warehouse->name,
            'code' => $warehouse->code,
        ];

        $response = $this->post(route('api.warehouses.index'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'code',
            ],
        ]);
    }
}

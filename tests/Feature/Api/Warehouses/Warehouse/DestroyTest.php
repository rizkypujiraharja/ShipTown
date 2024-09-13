<?php

namespace Tests\Feature\Api\Warehouses\Warehouse;

use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_destroy_call_returns_ok(): void
    {
        $warehouse = Warehouse::factory()->create();

        $response = $this->delete(route('api.warehouses.destroy', $warehouse));
        $response->assertSuccessful();

        $this->assertNull(Warehouse::find($warehouse->id));
    }
}

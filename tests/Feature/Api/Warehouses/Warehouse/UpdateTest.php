<?php

namespace Tests\Feature\Api\Warehouses\Warehouse;

use App\Models\Warehouse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_update_call_returns_ok(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Warehouse $newWarehouse */
        $newWarehouse = Warehouse::factory()->make();

        $data = [
            'name' => $newWarehouse->name,
            'code' => $newWarehouse->code,
            'tags' => ['tag1', 'tag2'],
        ];

        $response = $this->put(route('api.warehouses.update', $warehouse), $data);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'code',
            ],
        ]);

        $updatedWarehouse = Warehouse::find($warehouse->id);
        $this->assertEquals($updatedWarehouse->name, $newWarehouse->name);
        $this->assertEquals($updatedWarehouse->code, $newWarehouse->code);
    }
}

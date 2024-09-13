<?php

namespace Tests\Feature\Api\Admin\Users\User;

use App\Models\Warehouse;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->forceDelete();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin'));

        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $role = Role::findOrCreate('user', 'web');

        $warehouse = Warehouse::factory()->create();

        $data = [
            'name' => 'Test User',
            'role_id' => $role->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ];

        $response = $this->put(route('users.update', $user), $data);

        $response->assertStatus(200);
    }
}

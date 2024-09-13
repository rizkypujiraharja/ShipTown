<?php

namespace Tests\Feature\Api\Admin\Users;

use App\Models\Warehouse;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::factory()->create();
        $roles = Role::findOrCreate('admin');
        $admin->assignRole($roles);
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $warehouse = Warehouse::factory()->create();
        $testData = User::factory()->make()->toArray();
        $testData['role_id'] = Role::findOrCreate('user', 'web')->id;

        $response = $this->postJson(route('users.store'), $testData);

        $response->assertStatus(201);
    }

    public function test_add_deleted_user_return_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->delete();

        $response = $this->post(route('users.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => Role::findOrCreate('user', 'web')->id,
        ]);

        $response->assertStatus(200);
    }
}

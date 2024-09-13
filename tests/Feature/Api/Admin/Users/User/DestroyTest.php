<?php

namespace Tests\Feature\Api\Admin\Users\User;

use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    /** @test */
    public function test_destroy_call_returns_ok(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'api'));
        $admin->givePermissionTo(Permission::findOrCreate('manage users', 'api'));

        /** @var User $userToDelete */
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin, 'api')->delete('api/admin/users/'.$userToDelete->id);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id, 'deleted_at' => null]);
    }
}

<?php

namespace Tests\Feature\Api\Admin\Users;

use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'api'));

        $response = $this->actingAs($user, 'api')
            ->getJson(route('users.index', ['include' => 'roles']));

        $response->assertOk();

        $this->assertGreaterThan(0, $response->json('meta.total'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'printer_id',
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);
    }
}

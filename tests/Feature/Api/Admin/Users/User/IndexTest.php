<?php

namespace Tests\Feature\Api\Admin\Users\User;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_show_call_returns_ok(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson(route('users.show', $user->id));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }
}

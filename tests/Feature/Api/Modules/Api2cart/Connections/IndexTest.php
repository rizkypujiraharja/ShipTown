<?php

namespace Tests\Feature\Api\Modules\Api2cart\Connections;

use App\User;
use Tests\TestCase;

/**
 * Class IndexTest.
 */
class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson(route('api.modules.api2cart.connections.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                ],
            ],
        ]);
    }
}

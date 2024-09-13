<?php

namespace Tests\Feature\Api\Modules\DpdUk\DpdUkConnections\DpdUkConnection;

use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/dpd-uk/dpd-uk-connections/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $connection = \App\Modules\DpdUk\src\Models\Connection::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$connection->getKey());

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

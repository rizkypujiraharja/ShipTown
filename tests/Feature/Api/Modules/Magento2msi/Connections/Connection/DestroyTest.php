<?php

namespace Tests\Feature\Api\Modules\Magento2msi\Connections\Connection;

use App\Modules\Magento2msi\src\Models\Magento2msiConnection;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    private string $uri = 'api/modules/magento2msi/connections/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $connection = Magento2msiConnection::factory()->create();

        $response = $this->actingAs($user, 'api')->delete($this->uri.$connection->getKey());

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    /** @test */
    public function testUserAccess(): void
    {
        $connection = Magento2msiConnection::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson($this->uri.$connection->getKey(), []);

        ray($response->json());

        $response->assertForbidden();
    }
}

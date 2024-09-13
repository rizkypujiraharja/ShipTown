<?php

namespace Tests\Feature\Api\Modules\Magento2msi\Connections;

use App\Modules\Magento2MSI\src\Models\Magento2msiConnection;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/modules/magento2msi/connections/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        Magento2msiConnection::factory()->create();

        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }

    /** @test */
    public function testUserAccess(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        $response->assertForbidden();
    }
}

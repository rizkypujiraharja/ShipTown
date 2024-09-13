<?php

namespace Tests\Feature\Api\Modules\Magento2msi\Connections;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/modules/magento2msi/connections/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $connection = [
            'base_url' => 'https://example.com',
            'magento_source_code' => 'example',
            'inventory_source_warehouse_tag_id' => 1,
            'api_access_token' => 'example',
        ];

        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, $connection);

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
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, []);

        $response->assertForbidden();
    }
}

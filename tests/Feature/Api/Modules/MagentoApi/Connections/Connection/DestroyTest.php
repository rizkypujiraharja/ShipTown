<?php

namespace Tests\Feature\Api\Modules\MagentoApi\Connections\Connection;

use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\User;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    /** @test */
    public function test_destroy_call_returns_ok(): void
    {
        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'access_token_encrypted' => 'some-token',
        ]);

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->delete(route('api.modules.magento-api.connections.destroy', $connection));

        $response->assertOk();

        $this->assertFalse(MagentoConnection::where('id', $connection->id)->exists());
    }
}

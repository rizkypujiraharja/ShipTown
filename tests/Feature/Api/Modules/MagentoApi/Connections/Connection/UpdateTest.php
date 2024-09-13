<?php

namespace Tests\Feature\Api\Modules\MagentoApi\Connections\Connection;

use App\Models\Warehouse;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_success_config_create(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'api_access_token' => 'some-token',
        ]);

        $warehouse = Warehouse::firstOrCreate(['code' => '999'], ['name' => '999']);

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), [
                'base_url' => 'https://magento2.test2',
                'magento_store_id' => 1234562,
                'tag' => 'some-tag2',
                'pricing_source_warehouse_id' => $warehouse->id,
                'api_access_token' => 'some-token2',
            ]);

        $connection->refresh();

        $this->assertDatabaseHas('modules_magento2api_connections', [
            'id' => $connection->id,
            'base_url' => 'https://magento2.test2',
            'magento_store_id' => 1234562,
            'pricing_source_warehouse_id' => $warehouse->id,
        ]);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_failing_config_create(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $connection = MagentoConnection::create([
            'base_url' => 'https://magento2.test',
            'magento_store_id' => 123456,
            'pricing_source_warehouse_id' => 1,
            'api_access_token' => 'some-token',
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('put', route('api.modules.magento-api.connections.update', $connection), []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'base_url',
            'magento_store_id',
            // 'tag',
            // 'pricing_source_warehouse_id',
            'api_access_token',
        ]);
    }
}

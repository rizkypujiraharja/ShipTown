<?php

namespace Tests\Feature\Api\Modules\DpdUk\DpdUkConnections;

use App\Models\OrderAddress;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/modules/dpd-uk/dpd-uk-connections';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'account_number' => '123456789',
            'username' => 'test',
            'password' => 'test',
            'collection_address' => OrderAddress::factory()->create()->toArray(),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

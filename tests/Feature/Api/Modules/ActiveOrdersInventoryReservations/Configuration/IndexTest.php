<?php

namespace Tests\Feature\Api\Modules\ActiveOrdersInventoryReservations\Configuration;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/modules/active-orders-inventory-reservations/configuration';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri);

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
        $response = $this->actingAs($user, 'api')->getJson($this->uri);

        $response->assertForbidden();
    }
}

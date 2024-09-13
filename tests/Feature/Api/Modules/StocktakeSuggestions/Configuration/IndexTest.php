<?php

namespace Tests\Feature\Api\Modules\StocktakeSuggestions\Configuration;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = 'api/modules/stocktake-suggestions/configuration';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, []);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

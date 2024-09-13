<?php

namespace Tests\Feature\Api\Jobs;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private string $uri = '/api/jobs';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->getJson($this->uri, []);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertGreaterThan(0, $response->json('data'), 'No records returned');

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

<?php

namespace Tests\Feature\Api\PrintJobs;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/print-jobs';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, ['printer_id' => 1, 'content' => 'test content']);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}

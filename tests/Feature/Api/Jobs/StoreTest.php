<?php

namespace Tests\Feature\Api\Jobs;

use App\Models\ManualRequestJob;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/jobs';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user, 'api')->postJson($this->uri, ['job_id' => ManualRequestJob::first()->getKey()]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'message',
            'job_class',
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

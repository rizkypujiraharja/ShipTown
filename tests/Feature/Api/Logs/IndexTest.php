<?php

namespace Tests\Feature\Api\Logs;

use App\Models\Product;
use App\User;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        Activity::query()->forceDelete();

        $user = User::factory()->create();

        // product creation will generate some logs
        Product::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.logs.index'));

        $response->assertOk();

        $this->assertNotEquals(0, $response->json('meta.total'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'created_at',
                    'id',
                    'description',
                    'subject_id',
                    'subject_type',
                    'causer_id',
                    'causer_type',
                    'properties' => [
                        '*' => [],
                    ],
                    'changes' => [],
                ],
            ],
        ]);
    }
}

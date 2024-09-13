<?php

namespace Tests\Feature\Api\DataCollector;

use App\Models\DataCollection;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        DataCollection::factory()->create([
            'warehouse_id' => $warehouse->id,
            'name' => 'test',
        ]);

        $response = $this->actingAs($user, 'api')->getJson(route('api.data-collector.index'));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
        ]);
    }
}

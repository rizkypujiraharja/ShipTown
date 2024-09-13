<?php

namespace Tests\Feature\Api\InventoryMovements;

use App\Models\InventoryMovement;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        InventoryMovement::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.inventory-movements.index', [
            'include' => 'product,warehouse,user',
        ]));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',
                    'inventory_id',
                    'product_id',
                    'warehouse_id',
                    'quantity_delta',
                    'quantity_before',
                    'quantity_after',
                    'description',
                    'user',
                    'warehouse',
                    'product',
                ],
            ],
        ]);
    }
}

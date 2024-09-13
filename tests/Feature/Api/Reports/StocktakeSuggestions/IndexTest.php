<?php

namespace Tests\Feature\Api\Reports\StocktakeSuggestions;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StocktakeSuggestion;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_pagination_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Product::factory()->create();
        Warehouse::factory()->create();

        foreach (range(1, 5) as $index) {
            StocktakeSuggestion::factory()->create(
                [
                    'inventory_id' => Inventory::first()->getKey(),
                    'points' => 1,
                    'reason' => 'test'.$index,
                ],
            );
        }

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/stocktake-suggestions?page=2&per_page=1');

        $response->assertOk();
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        Product::factory()->create();
        Warehouse::factory()->create();

        StocktakeSuggestion::factory()->create(
            [
                'inventory_id' => Inventory::first()->getKey(),
                'points' => 1,
                'reason' => 'test',
            ],
        );
        $response = $this->actingAs($user, 'api')
            ->getJson('/api/reports/stocktake-suggestions?select=inventory_id,points,reason');

        $response->assertOk();
    }
}

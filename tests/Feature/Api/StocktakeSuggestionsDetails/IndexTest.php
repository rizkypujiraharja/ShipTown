<?php

namespace Tests\Feature\Api\StocktakeSuggestionsDetails;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StocktakeSuggestion;
use App\Models\Warehouse;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $inventory = Inventory::query()->where([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ])
            ->first();

        StocktakeSuggestion::query()->create([
            'inventory_id' => $inventory->id,
            'points' => 1,
            'reason' => 'test',
        ]);

        $response = $this->actingAs($user, 'api')->getJson(route('api.stocktake-suggestions-details.index'));

        $response->assertOk();

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'points',
                    'reason',
                ],
            ],
        ]);
    }
}

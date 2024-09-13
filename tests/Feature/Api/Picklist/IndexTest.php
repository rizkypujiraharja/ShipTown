<?php

namespace Tests\Feature\Api\Picklist;

use App\Models\OrderProduct;
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
        $warehouse = Warehouse::factory()->create();
        //        OrderProduct::query()->forceDelete();
        $orderProduct = OrderProduct::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.picklist.index'));

        $response->assertOk();

        $this->assertNotEquals(0, $response->json('meta.total'));

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'product_id',
                    'name_ordered',
                    'sku_ordered',
                    'total_quantity_to_pick',
                    'inventory_source_shelf_location',
                    'inventory_source_quantity',
                    'quantity_required',
                    'order_product_ids' => [],
                ],
            ],
        ]);
    }
}

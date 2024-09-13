<?php

namespace Tests\Feature\Api\Reports\InventoryTransfers;

use App\Models\DataCollectionRecord;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_pagination_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();

        Product::factory()->create();
        DataCollectionRecord::factory()->create([
            'product_id' => Product::first()->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/inventory-transfers?page=1&per_page=1');

        $response->assertOk();
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $user = User::factory()->create();

        $warehouse = Warehouse::factory()->create();
        Product::factory()->create();
        DataCollectionRecord::factory()->create([
            'product_id' => Product::first()->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_code' => $warehouse->code,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/reports/inventory-transfers');

        $response->assertOk();
    }
}

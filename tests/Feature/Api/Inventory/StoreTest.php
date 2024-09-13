<?php

namespace Tests\Feature\Api\Inventory;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $inventory = Inventory::first();

        $params = [
            'id' => $inventory->getKey(),
            'shelve_location' => 'test',
        ];

        $response = $this->post(url('api/inventory'), $params);

        ray($response->json());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'quantity',
                    'quantity_reserved',
                    'product_id',
                    'warehouse_code',
                    'updated_at',
                    'created_at',
                    'id',
                    'quantity_available',
                ],
            ],
        ]);
    }

    public function testIfCantPostWithoutData(): void
    {
        $response = $this->postJson(url('api/inventory'), []);

        $response->assertStatus(422);
    }

    public function testQuantityUpdate(): void
    {
        Passport::actingAs(
            User::factory()->create()
        );

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        $inventory = Inventory::first();

        $update = [
            'id' => $inventory->getKey(),
            'quantity' => rand(100, 200),
            'quantity_reserved' => rand(10, 50),
        ];

        $response = $this->postJson(route('api.inventory.store'), $update);

        $response->assertStatus(200);
    }
}

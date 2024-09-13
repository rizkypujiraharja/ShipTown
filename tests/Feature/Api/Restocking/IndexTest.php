<?php

namespace Tests\Feature\Api\Restocking;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_product_has_tags_containing_filter(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('fulfilment');

        Product::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->attachTag('test WH');

        /** @var User $user */
        $user = User::factory()->create();

        Inventory::query()->update(['quantity' => 1]);

        $response = $this->actingAs($user, 'api')
            ->getJson(route('api.restocking.index', [
                'filter[warehouse_id]' => $warehouse->getKey(),
                'filter[product_has_tags_containing]' => 'WH',
            ]));

        ray($response->json());

        $response->assertSuccessful();

        $this->assertCount(1, $response->json('data'));
    }

    public function test_product_has_tags_filter(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('fulfilment');

        Product::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->attachTag('test');

        /** @var User $user */
        $user = User::factory()->create();

        Inventory::query()->update(['quantity' => 1]);

        ray()->showApp();
        ray()->showQueries();

        ray($warehouse->getKey());
        $response = $this->actingAs($user, 'api')
            ->getJson(route('api.restocking.index', [
                'filter[warehouse_id]' => $warehouse->getKey(),
                'filter[product_has_tags]' => 'test',
            ]));

        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        ray()->showApp();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();
        $warehouse->attachTag('fulfilment');

        InventoryReservationsEventServiceProviderBase::enableModule();

        Product::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        Inventory::query()->update(['quantity' => 1]);

        $response = $this->actingAs($user, 'api')->getJson(route('api.restocking.index'));

        ray($response->json());

        $response->assertOk();

        $this->assertCount(2, $response->json('data'));

        $response->assertJsonStructure([
            'meta',
            'data' => [
                '*' => [
                    'warehouse_code',
                    'product_sku',
                    'product_name',
                    'quantity_required',
                    'quantity_available',
                    'quantity_incoming',
                    'reorder_point',
                    'restock_level',
                    'warehouse_quantity',
                ],
            ],
        ]);
    }
}

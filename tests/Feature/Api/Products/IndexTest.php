<?php

namespace Tests\Feature\Api\Products;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function testProductHasTagsFilter(): void
    {
        ray()->showQueries();
        $tagName = 'fdsaj & fdajJ';

        Product::query()->forceDelete();

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->attachTag($tagName);

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.products.index', [
            'include' => 'tags',
            'filter[has_tags]' => $tagName,
        ]));

        $response->assertOk();

        ray($response->json('data'));

        $this->assertCount(1, $response->json('data'), 'No records returned');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sku',
                    'name',
                    'price',
                    'sale_price',
                    'sale_price_start_date',
                    'sale_price_end_date',
                    'quantity',
                    'quantity_reserved',
                    'quantity_available',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                    'inventory_source_shelf_location',
                    'inventory_source_quantity',
                    'inventory_source_product_id',
                    'inventory_source_location_id',
                ],
            ],
        ]);
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        Product::query()->forceDelete();
        Product::factory()->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson(route('api.products.index', [
            'include' => [
                'inventory',
                'aliases',
                'tags',
            ],
            'filter[inventory_source_warehouse_code]' => 1,
        ]));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'sku',
                    'name',
                    'price',
                    'sale_price',
                    'sale_price_start_date',
                    'sale_price_end_date',
                    'quantity',
                    'quantity_reserved',
                    'quantity_available',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                    'inventory_source_shelf_location',
                    'inventory_source_quantity',
                    'inventory_source_product_id',
                    'inventory_source_location_id',
                    'inventory' => [
                        '*' => [],
                    ],
                    'aliases' => [
                        '*' => [],
                    ],
                    'tags' => [
                        '*' => [],
                    ],
                ],
            ],
        ]);
    }
}

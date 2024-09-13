<?php

namespace Tests\Feature\Api\Products;

use App\Models\Product;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_new_product_return_ok(): void
    {
        $params = [
            'sku' => 'TestSku',
            'name' => 'Product Name',
            'price' => 200,
        ];

        $response = $this->post('api/products', $params);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'name',
            'price',
            'sale_price',
            'sale_price_start_date',
            'sale_price_end_date',
            'quantity',
            'quantity_reserved',
            'quantity_available',
            'updated_at',
            'created_at',
            'id',
        ]);
    }

    public function test_store_available_product_return_ok(): void
    {
        $product = Product::factory()->create();
        $params = [
            'sku' => $product->sku,
            'name' => 'Product Name',
            'price' => 200,
        ];

        $response = $this->post('api/products', $params);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'name',
            'price',
            'sale_price',
            'sale_price_start_date',
            'sale_price_end_date',
            'quantity',
            'quantity_reserved',
            'quantity_available',
            'updated_at',
            'created_at',
            'id',
        ]);
    }
}

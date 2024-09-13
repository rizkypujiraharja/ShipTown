<?php

namespace Tests\Feature\Api\ProductsAliases;

use App\Models\Product;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = '/api/products-aliases';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        $user = User::factory()->create()->assignRole('admin');

        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'product_id' => $product->id,
            'alias' => fake()->ean8(),
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    /** @test */
    public function testUserAccess(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->postJson($this->uri, []);

        $response->assertForbidden();
    }
}

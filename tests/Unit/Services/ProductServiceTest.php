<?php

namespace Tests\Unit\Services;

use App\Models\ProductAlias;
use App\Services\ProductService;
use App\User;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\ModelSample;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    public function testFindByAlias(): void
    {
        $alias = ProductAlias::factory()->create();

        $this->assertNotNull(
            ProductService::findByAlias($alias->alias)
        );
    }

    /**
     * A basic feature test example.
     */
    public function testIfReservesCorrectly(): void
    {
        Passport::actingAs(
            User::factory()->create()->assignRole('admin')
        );

        Event::fake();

        $quantity_to_reserve = 10;

        $product_before = $this->json('POST', 'api/products', ModelSample::PRODUCT)
            ->assertStatus(200)
            ->getContent();

        $product_before = json_decode($product_before, true);

        ProductService::reserve(ModelSample::PRODUCT['sku'], $quantity_to_reserve, 'Reservation test');

        $product_after = $this->json('POST', 'api/products', ModelSample::PRODUCT)
            ->assertStatus(200)
            ->getContent();

        $product_after = json_decode($product_after, true);

        $quantity_reserved_diff = $product_after['quantity_reserved'] - $product_before['quantity_reserved'];

        $this->assertEquals($quantity_to_reserve, $quantity_reserved_diff);
    }

    /**
     * A basic feature test example.
     */
    public function testIfReleasesQuantityCorrectly(): void
    {
        Passport::actingAs(
            User::factory()->create()->assignRole('admin')
        );

        Event::fake();

        $quantity_to_release = 10;

        $product_before = $this->json('POST', 'api/products', ModelSample::PRODUCT)
            ->assertStatus(200)
            ->getContent();

        $product_before = json_decode($product_before, true);

        ProductService::release(ModelSample::PRODUCT['sku'], $quantity_to_release, 'Reservation test');

        $product_after = $this->json('POST', 'api/products', ModelSample::PRODUCT)
            ->assertStatus(200)
            ->getContent();

        $product_after = json_decode($product_after, true);

        $quantity_reserved_diff = $product_before['quantity_reserved'] - $product_after['quantity_reserved'];

        $this->assertEquals($quantity_to_release, $quantity_reserved_diff);
    }
}

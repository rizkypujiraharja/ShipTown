<?php

namespace Tests\Unit\Models;

use App\Events\Product\ProductTagAttachedEvent;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    public function test_if_dispatched_tag_attached_event(): void
    {
        Event::fake(ProductTagAttachedEvent::class);

        /** @var Product $product */
        $product = Product::factory()->create();

        $product->attachTag('test-fads');

        Event::assertDispatched(ProductTagAttachedEvent::class);
    }

    public function testIfQuantityAvailableBelow0NotAllowed(): void
    {
        $product_before = Product::firstOrCreate(['sku' => '0123456']);

        // reserve 1 more than actually in stock
        // so quantity_available < 0
        ProductService::reserve(
            '0123456',
            $product_before->quantity + 1,
            'ProductModeTest reservation'
        );

        $product_after = $product_before->fresh();

        $this->assertEquals(0, $product_after->quantity_available);
    }

    /**
     * A basic feature test example.
     */
    public function testIfReservesCorrectly(): void
    {
        $product_before = Product::firstOrCreate(['sku' => '0123456']);

        ProductService::reserve(
            '0123456',
            5,
            'ProductModeTest reservation'
        );

        $product_after = $product_before->fresh();

        $this->assertEquals($product_after->quantity_reserved, $product_before->quantity_reserved + 5);
    }
}

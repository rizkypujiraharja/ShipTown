<?php

namespace Tests\Unit\Modules\AutoTags;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\AutoTags\src\EventServiceProviderBase;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Warehouse::factory()->create();

        InventoryTotalsServiceProvider::enableModule();
        EventServiceProviderBase::enableModule();
    }

    public function test_if_attaches_tag(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        $product->inventory->first()->update([
            'quantity' => 0,
            'quantity_reserved' => 1,
        ]);

        $product = $product->refresh();

        $this->assertTrue(true);
    }

    public function test_if_detaches_tag(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        $product->inventory->first()->update([
            'quantity' => 0,
            'quantity_reserved' => 0,
        ]);

        $this->assertFalse($product->hasTags(['oversold']));
    }
}

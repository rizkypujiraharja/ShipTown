<?php

namespace Tests\Unit\Modules\Api2cart;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use App\Modules\Api2cart\src\Transformers\ProductTransformer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetQuantityTest extends TestCase
{
    use RefreshDatabase;

    private Api2cartProductLink $productLink;

    protected function setUp(): void
    {
        parent::setUp();

        Warehouse::factory()->count(10)->create();

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Inventory::query()->each(function (Inventory $inventory) {
            $inventory->update(['quantity' => 1]);
        });

        $api2cartConnection = Api2cartConnection::factory()
            ->create(['inventory_source_warehouse_tag' => 'magento_stock']);

        $this->productLink = new Api2cartProductLink;
        $this->productLink->api2cart_connection_id = $api2cartConnection->getKey();
        $this->productLink->product_id = $product1->getKey();
        $this->productLink->save();
    }

    /**
     * A basic feature test example.
     */
    public function testEmptyInventoryWarehouseIds(): void
    {
        $this->assertEquals(0, ProductTransformer::toApi2cartPayload($this->productLink)['quantity']);
    }

    /**
     * A basic feature test example.
     */
    public function testSetInventoryWarehouseIds(): void
    {
        Warehouse::query()
            ->select('id')
            ->limit(5)
            ->inRandomOrder()
            ->get()
            ->map(function (Warehouse $warehouse) {
                $warehouse->attachTag('magento_stock');

                return $warehouse->getKey();
            })
            ->toArray();

        $toApi2cartPayload = ProductTransformer::toApi2cartPayload($this->productLink);

        $this->assertEquals(5, $toApi2cartPayload['quantity']);
    }
}

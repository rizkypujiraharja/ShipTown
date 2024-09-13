<?php

namespace Tests\External\Api2cart\Jobs;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Jobs\SyncProductsJob;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncProductsToApi2cartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create(['code' => '99', 'name' => '99']);
        $warehouse->attachTag('magento_stock');

        $product = Product::factory()
            ->with('inventory')
            ->create();

        Api2cartConnection::factory()->create([
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
            'magento_store_id' => null,
            'inventory_source_warehouse_tag' => 'magento_stock',
            'pricing_source_warehouse_id' => $warehouse->getKey(),
        ]);

        $product->attachTags(['Available Online']);

        SyncProductsJob::dispatchSync();
    }
}

<?php

namespace Tests\External\Api2cart\Api;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Jobs\SyncProduct;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class ProductUpdateTest
 */
class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Api2cartProductLink $api2cart_product_link;

    private Api2cartConnection $api2cart_connection;

    private Product $product;

    private Inventory $inventory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create(['code' => '99', 'name' => '99']);

        $warehouse->attachTag('magento_stock');

        $product = Product::factory()->create();

        $api2cart_connection = Api2cartConnection::factory()->create([
            'pricing_location_id' => '99',
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
            'inventory_source_warehouse_tag' => 'magento_stock',
            'pricing_source_warehouse_id' => $warehouse->getKey(),
        ]);

        $this->api2cart_product_link = Api2cartProductLink::factory()->create([]);
        $this->api2cart_product_link->product()->associate($product);
        $this->api2cart_product_link->api2cartConnection()->associate($api2cart_connection);
    }

    public function test_if_updates_stock_if_no_location_source_specified(): void
    {
        $job = new SyncProduct($this->api2cart_product_link);
        $job->handle();
    }
}

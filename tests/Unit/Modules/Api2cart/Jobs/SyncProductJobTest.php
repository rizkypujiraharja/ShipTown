<?php

namespace Tests\Unit\Modules\Api2cart\Jobs;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Jobs\SyncProduct;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Tests\TestCase;

class SyncProductJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        Api2cartConnection::query()->forceDelete();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        Api2cartConnection::factory()->create([
            'pricing_location_id' => $warehouse->code,
        ]);

        Api2cartProductLink::where(['product_id' => $product->getKey()])
            ->each(function (Api2cartProductLink $product_link) {
                SyncProduct::dispatchSync($product_link);
            });

        // no errors, so we are happy :)
        $this->assertTrue(true);
    }
}

<?php

namespace Tests\External\Api2cart\Jobs;

use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Api2cart\src\Exceptions\RequestException;
use App\Modules\Api2cart\src\Jobs\FetchSimpleProductsInfoJob;
use App\Modules\Api2cart\src\Jobs\SyncProductsJob;
use App\Modules\Api2cart\src\Jobs\UpdateMissingTypeAndIdJob;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Tests\TestCase;

class FetchSimpleProductsInfoJobTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     *
     * @throws RequestException
     */
    public function testExample(): void
    {
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $api2cartConnection = Api2cartConnection::factory()->create([
            'pricing_source_warehouse_id' => $warehouse->getKey(),
        ]);

        $productLink1 = Api2cartProductLink::factory()->create([
            'api2cart_connection_id' => $api2cartConnection->getKey(),
            'product_id' => Product::factory()->create()->getKey(),
        ]);

        $productLink2 = Api2cartProductLink::factory()->create([
            'api2cart_connection_id' => $api2cartConnection->getKey(),
            'product_id' => Product::factory()->create()->getKey(),
        ]);

        UpdateMissingTypeAndIdJob::dispatch();

        SyncProductsJob::dispatch();

        Api2cartProductLink::query()->update(['last_fetched_data' => null]);

        FetchSimpleProductsInfoJob::dispatch();

        $this->assertDatabaseMissing('modules_api2cart_product_links', ['last_fetched_data' => null]);
    }
}

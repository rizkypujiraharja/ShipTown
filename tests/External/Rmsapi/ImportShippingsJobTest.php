<?php

namespace Tests\External\Rmsapi;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Rmsapi\src\Jobs\ImportShippingsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use GuzzleHttp\Exception\GuzzleException;
use Tests\TestCase;

class ImportShippingsJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        OrderStatus::factory()->create([
            'code' => 'cancelled',
            'name' => 'cancelled',
            'order_active' => false,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function test_endpoint(): void
    {
        $connection = RmsapiConnection::factory()->create([
            'location_id' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
            'url' => env('TEST_RMSAPI_URL'),
            'username' => env('TEST_RMSAPI_USERNAME'),
            'password' => env('TEST_RMSAPI_PASSWORD'),
        ]);

        Product::create(['sku' => '45']);
        Warehouse::create([
            'code' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
            'name' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
        ]);

        ImportShippingsJob::dispatch($connection->getKey());

        ray(Order::all()->toArray());
        ray(OrderProduct::all()->toArray());

        $this->assertNotEquals(0, Order::query()->count());
    }
}

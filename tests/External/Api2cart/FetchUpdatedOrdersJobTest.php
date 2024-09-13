<?php

namespace Tests\External\Api2cart;

use App\Modules\Api2cart\src\Jobs\ImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FetchUpdatedOrdersJobTest extends TestCase
{
    public function testIfJobRunsWithoutExceptions(): void
    {
        $this->expectNotToPerformAssertions();

        Bus::fake();
        Event::fake();

        // we want to test on clean data
        Api2cartConnection::query()->delete();

        // we set key to api2cart demo store
        $api2cartConnection = new Api2cartConnection([
            'location_id' => '99',
            'type' => 'opencart',
            'url' => 'http://demo.api2cart.com/opencart',
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
            'inventory_source_warehouse_tag' => 'magento_stock',
        ]);

        $api2cartConnection->save();

        $job = new ImportOrdersJobs($api2cartConnection);

        $job->handle();
    }
}

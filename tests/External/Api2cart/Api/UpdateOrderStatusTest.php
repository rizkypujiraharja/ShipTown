<?php

namespace Tests\External\Api2cart\Api;

use App\Modules\Api2cart\src\Api\Orders;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws GuzzleException
     */
    public function testIfJobRunsWithoutExceptions(): void
    {
        // we set key to api2cart demo store
        $api2cartConnection = new Api2cartConnection([
            'location_id' => '99',
            'type' => 'opencart',
            'url' => 'https://demo.api2cart.com/opencart',
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
            'inventory_source_warehouse_tag' => 'magento_stock',
        ]);

        $api2cartConnection->save();

        // randomly select one status
        $statuses = Orders::statuses($api2cartConnection->bridge_api_key, [])->getResult();
        $statuses = $statuses['cart_order_statuses'];
        $status = $statuses[rand(0, count($statuses) - 1)];

        // select an order
        $order = Orders::get($api2cartConnection->bridge_api_key, ['count' => 1])[0];

        Orders::update($api2cartConnection->bridge_api_key, [
            'order_id' => $order['order_id'],
            'order_status' => $status['id'],
        ]);

        // refresh the order
        $order = Orders::get($api2cartConnection->bridge_api_key, [
            'order_ids' => $order['order_id'],
            'count' => 1,
        ])[0];

        $this->assertEquals($order['status']['id'], $status['id']);
    }
}

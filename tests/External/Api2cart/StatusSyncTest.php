<?php

namespace Tests\External\Api2cart;

use App\Models\Order;
use App\Modules\Api2cart\src\Api\Orders;
use App\Modules\Api2cart\src\Api\OrderStatus;
use App\Modules\Api2cart\src\Api2cartServiceProvider;
use App\Modules\Api2cart\src\Exceptions\RequestException;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use App\Modules\Api2cart\src\Models\Api2cartConnection;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_store_key_is_configured(): void
    {
        $this->assertNotEmpty(env('API2CART_API_KEY'), 'API2CART_API_KEY env key not set');
        $this->assertNotEmpty(config('api2cart.api2cart_test_store_key'), 'TEST_API2CART_STORE_KEY env key not set');
    }

    /**
     * A basic feature test example.
     *
     *
     * @throws RequestException
     * @throws GuzzleException
     */
    public function test_if_syncs_status(): void
    {
        Api2cartServiceProvider::enableModule();

        // we set key to api2cart demo store
        $api2cartConnection = new Api2cartConnection([
            'location_id' => '99',
            'type' => 'magento',
            'url' => 'https://demo.api2cart.com/opencart',
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
        ]);
        $api2cartConnection->save();

        DispatchImportOrdersJobs::dispatchSync();

        $response = OrderStatus::list($api2cartConnection->bridge_api_key);

        $orderStatusList = $response->getResult()['cart_order_statuses'];

        $order = Order::first();

        do {
            $randomStatus = $orderStatusList[rand(0, count($orderStatusList) - 1)];
        } while ($order->status_code === $randomStatus['id']);

        \App\Models\OrderStatus::updateOrCreate([
            'code' => $randomStatus['id'],
        ], [
            'name' => $randomStatus['id'],
            'sync_ecommerce' => true,
        ]);

        $order->status_code = $randomStatus['id'];
        $order->save();

        $response = Orders::list($api2cartConnection->bridge_api_key, ['ids' => $order->order_number]);

        $this->assertEquals($order->status_code, $response->getResult()['order'][0]['status']['id']);
    }

    /**
     * A basic feature test example.
     *
     *
     * @throws RequestException
     * @throws GuzzleException
     */
    public function test_if_not_syncs_status_when_disabled(): void
    {
        Api2cartServiceProvider::enableModule();

        // we set key to api2cart demo store
        $api2cartConnection = new Api2cartConnection([
            'location_id' => '99',
            'type' => 'magento',
            'url' => 'https://demo.api2cart.com/opencart',
            'bridge_api_key' => config('api2cart.api2cart_test_store_key'),
        ]);
        $api2cartConnection->save();

        $response = OrderStatus::list($api2cartConnection->bridge_api_key);

        $orderStatusList = $response->getResult()['cart_order_statuses'];

        DispatchImportOrdersJobs::dispatchSync();
        $order = Order::first();

        do {
            $randomStatus = $orderStatusList[rand(0, count($orderStatusList) - 1)];
        } while ($order->status_code === $randomStatus['id']);

        \App\Models\OrderStatus::updateOrCreate([
            'code' => $randomStatus['id'],
        ], [
            'name' => $randomStatus['id'],
            'sync_ecommerce' => false,
        ]);

        $order->status_code = $randomStatus['id'];
        $order->save();

        $response = Orders::list($api2cartConnection->bridge_api_key, ['ids' => $order->order_number]);

        $this->assertNotEquals($order->status_code, $response->getResult()['order'][0]['status']['id']);
    }
}

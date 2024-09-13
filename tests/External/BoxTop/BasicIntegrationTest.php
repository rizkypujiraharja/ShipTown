<?php

namespace Tests\External\BoxTop;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\BoxTop\src\Api\ApiClient;
use App\Modules\BoxTop\src\Models\WarehouseStock;
use App\Modules\BoxTop\src\Services\BoxTopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_successful_order_to_pick_integration(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();
        $order->shippingAddress->company = 'BoxTop Technologies Ltd';
        $order->shippingAddress->full_name = 'Reece Ipient';
        $order->shippingAddress->address1 = 'Providence House';
        $order->shippingAddress->address2 = '2 River Street';
        $order->shippingAddress->city = 'Windsor';
        $order->shippingAddress->state_code = 'Berkshire';
        $order->shippingAddress->postcode = 'SL4 1QT';
        $order->shippingAddress->country_code = 'GB';
        $order->shippingAddress->phone = '+44 20 8400 2000';
        $order->shippingAddress->save();

        BoxTopService::refreshBoxTopWarehouseStock();

        $this->assertTrue(WarehouseStock::query()->exists(), 'No warehouse records fetched');

        /** @var WarehouseStock $randomProduct */
        $randomProduct = WarehouseStock::query()
            ->where('Available', '>', 1)
            ->inRandomOrder()
            ->first();

        /** @var Product $product */
        $product = Product::factory()->create([
            'sku' => $randomProduct->SKUNumber,
            'name' => $randomProduct->SKUName,
        ]);

        OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'sku_ordered' => $product->sku,
            'name_ordered' => $product->name,
            'quantity_ordered' => 1,
        ]);

        $result = BoxTopService::postOrder($order);

        $this->assertTrue($result->http_response->getStatusCode() == 201);
    }

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        $boxtop = new ApiClient;

        $apiResponse = $boxtop->getAllProducts();

        $this->assertEquals(200, $apiResponse->http_response->getStatusCode());
    }
}

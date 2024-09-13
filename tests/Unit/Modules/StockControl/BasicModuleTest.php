<?php

namespace Tests\Unit\Modules\StockControl;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\StockControl\src\StockControlServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_decreases_quantity_when_product_shipped(): void
    {
        InventoryMovementsServiceProvider::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create();

        StockControlServiceProvider::enableModule();

        $orderProductShipment = new OrderProductShipment;
        $orderProductShipment->quantity_shipped = $orderProduct->quantity_to_ship * (-1);
        $orderProductShipment->orderProduct()->associate($orderProduct);
        $orderProductShipment->order()->associate($order);
        $orderProductShipment->product()->associate($product);
        $orderProductShipment->warehouse()->associate($warehouse);
        $orderProductShipment->save();

        $inventory = Inventory::whereWarehouseId($warehouse->getKey())->whereProductId($product->getKey())->first();

        $this->assertNotNull($inventory);
        $this->assertEquals($orderProduct->quantity_to_ship, $inventory->quantity);
    }
}

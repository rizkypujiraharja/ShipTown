<?php

namespace Tests\Unit\Modules\StockControl;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\OrderProduct;
use App\Models\OrderProductShipment;
use App\Models\Warehouse;
use App\Modules\StockControl\src\StockControlServiceProvider;
use Tests\TestCase;

class OrderProductShipmentTest extends TestCase
{
    public function testExample(): void
    {
        StockControlServiceProvider::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create();

        OrderProductShipment::create([
            'warehouse_id' => $warehouse->id,
            'order_id' => $orderProduct->order_id,
            'order_product_id' => $orderProduct->id,
            'product_id' => $orderProduct->product_id,
            'quantity_shipped' => 4,
        ]);

        ray(InventoryMovement::all()->toArray());

        ray(Inventory::all()->toArray());

        /** @var Inventory $inventory */
        $inventory = $orderProduct->product->inventory($warehouse->code)->first();

        $this->assertEquals(-4, $inventory->quantity);
    }
}

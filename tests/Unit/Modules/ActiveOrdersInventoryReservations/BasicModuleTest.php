<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    /** @test */
    public function testIfUpdatesInventoryReservationWhenOrderProductsIsModifiedOnActiveOrder(): void
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['order_active' => true]);

        // doing something
        $order = Order::factory()->create(['is_active' => true, 'status_code' => $orderStatus->code]);

        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct1->update(['quantity_shipped' => $orderProduct1->quantity_ordered]);
        $orderProduct1->refresh();

        // assert something
        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct1),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct1->sku_ordered,
            'quantity_reserved' => $orderProduct1->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct2),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct2->sku_ordered,
            'quantity_reserved' => $orderProduct2->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);
    }

    /** @test */
    public function testIfDeletesInventoryReservationWhenOrderProductsIsDeletedFromActiveOrder(): void
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['order_active' => true]);

        // doing something
        $order = Order::factory()->create(['is_active' => true, 'status_code' => $orderStatus->code]);

        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        // assert something
        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct1),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct1->sku_ordered,
            'quantity_reserved' => $orderProduct1->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct2),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct2->sku_ordered,
            'quantity_reserved' => $orderProduct2->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);

        $orderProduct1->delete();

        // assert something
        $this->assertDatabaseMissing('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct1),
        ]);
    }

    /** @test */
    public function testIfReservesInventoryWhenOrderProductsIsAddedToActiveOrder(): void
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();

        $product = Product::factory()->create();

        $configuration = Configuration::firstOrCreate([]);
        $configuration->update(['warehouse_id' => $warehouse->getKey()]);

        $inventory = Inventory::where([
            'product_id' => $product->getKey(),
            'warehouse_id' => $warehouse->getKey(),
        ])->first();

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['order_active' => true]);

        /** @var Order $order */
        $order = Order::factory()->create(['is_active' => true, 'status_code' => $orderStatus->code]);

        // doing something
        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        // assert something
        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct1),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct1->sku_ordered,
            'quantity_reserved' => $orderProduct1->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'custom_uuid' => ReservationsService::getUuid($orderProduct2),
            'inventory_id' => $inventory->id,
            'warehouse_code' => $warehouse->code,
            'product_sku' => $orderProduct2->sku_ordered,
            'quantity_reserved' => $orderProduct2->quantity_to_ship,
            'comment' => 'Order #'.$order->order_number, // Order #1
        ]);
    }

    /** @test */
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

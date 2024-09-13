<?php

namespace Tests\Unit\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\Services\OrderService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testIfUpdatesProductsQuantitiesAreCorrect(): void
    {
        $orderFake = Order::factory()->make();
        $randomCount = rand(1, 10);

        OrderAddress::query()->forceDelete();
        Order::query()->forceDelete();

        $attributes = [
            'order_number' => $orderFake->order_number,
            'status_code' => $orderFake->status_code,
            'total' => 12.40,
            'total_paid' => 12.40,
            'order_products' => OrderProduct::factory()->count(2)->make()->toArray(),
            'shipping_address' => OrderAddress::factory()->make()->toArray(),
        ];

        $quantityExpected = collect($attributes['order_products'])->sum(function ($orderProduct) {
            return $orderProduct['quantity_ordered'];
        });

        $order = OrderService::updateOrCreate($attributes);
        $this->assertEquals($quantityExpected, OrderProduct::sum('quantity_ordered'));

        // we will run one update and see if quantities are updated correctly

        $attributes['order_products'] = array_merge(
            $attributes['order_products'],
            OrderProduct::factory()->count($randomCount)->make()->toArray()
        );

        $quantityExpected = collect($attributes['order_products'])->sum(function ($orderProduct) {
            return $orderProduct['quantity_ordered'];
        });

        $order = OrderService::updateOrCreate($attributes);
        $this->assertEquals($quantityExpected, OrderProduct::sum('quantity_ordered'));
    }

    public function testIfUpdatesProductsCorrectly(): void
    {
        $orderFake = Order::factory()->make();
        $randomCount = rand(1, 10);

        OrderAddress::query()->forceDelete();
        Order::query()->forceDelete();

        $attributes = [
            'order_number' => $orderFake->order_number,
            'status_code' => $orderFake->status_code,
            'total' => 12.40,
            'total_paid' => 12.40,
            'order_products' => OrderProduct::factory()->count(2)->make()->toArray(),
            'shipping_address' => OrderAddress::factory()->make()->toArray(),
        ];

        $order = OrderService::updateOrCreate($attributes);
        $this->assertEquals(2, OrderProduct::count());

        $attributes['order_products'] = array_merge(
            $attributes['order_products'],
            OrderProduct::factory()->count($randomCount)->make()->toArray()
        );

        $order = OrderService::updateOrCreate($attributes);
        $equals = 2 + $randomCount === OrderProduct::count();

        //        if (!$equals) {
        //            dd($attributes);
        //        }

        $this->assertTrue($equals);
    }

    public function testStatusCodeUpdate(): void
    {
        // some automations listening to event might
        // automatically change status
        // this way we disable it
        Event::fake();

        $orderFake = Order::factory()->make();

        OrderAddress::query()->forceDelete();
        Order::query()->forceDelete();

        $attributes = [
            'order_number' => $orderFake->order_number,
            'status_code' => $orderFake->status_code,
            'total' => 12.40,
            'total_paid' => 12.40,
            'order_products' => OrderProduct::factory()->count(2)->make()->toArray(),
            'shipping_address' => OrderAddress::factory()->make()->toArray(),
        ];

        $order = OrderService::updateOrCreate($attributes);

        $this->assertNotNull($order);

        $this->assertEquals($orderFake->status_code, $order->status_code);
    }

    /**
     * A basic feature test example.
     */
    public function testSimplestCreation(): void
    {
        OrderAddress::query()->forceDelete();
        OrderProduct::query()->forceDelete();
        Order::query()->forceDelete();

        $order = Order::factory()->make();

        $attributes = [
            'order_number' => $order->order_number,
            'order_products' => [],
            'shipping_address' => [],
        ];

        $order = OrderService::updateOrCreate($attributes);

        $this->assertNotNull($order);
    }

    /**
     * A basic feature test example.
     */
    public function testCanFulfillMethod(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        ray()->showQueries();
        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create();

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()
            ->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
            ]);

        $inventory = $orderProduct->product->inventory($warehouse->code)->first();

        $inventory->update([
            'quantity' => $orderProduct->quantity_ordered,
            'quantity_available' => 0,
            'quantity_reserved' => 0,
        ]);

        $this->assertTrue(OrderService::canFulfill($order), 'a');
        $this->assertFalse(OrderService::canNotFulfill($order), 'b');

        $this->assertTrue(OrderService::canFulfill($order, $warehouse->code), 'c');
        $this->assertFalse(OrderService::canFulfill($order, 99), 'd');
    }

    /**
     * A basic feature test example.
     */
    public function testCanNotFulfillMethod(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        Order::query()->forceDelete();
        Product::query()->forceDelete();
        Inventory::query()->forceDelete();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create();

        OrderProduct::factory(1)
            ->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
            ]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = $order->orderProducts->first();

        $orderProduct->product->inventory($warehouse->code)->update([
            'quantity' => 0,
            'quantity_reserved' => 0,
        ]);

        $this->assertTrue(OrderService::canNotFulfill($order), 'Should not be able to fulfill from any location');
        $this->assertTrue(OrderService::canNotFulfill($order, $warehouse->code), 'Should not be able to fulfill from location 99');
    }

    /**
     * A basic feature test example.
     */
    public function testFailedCanFulfill(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create();

        OrderProduct::factory(1)
            ->create(['order_id' => $order->id]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = $order->orderProducts->first();

        $orderProduct->product->inventory($warehouse->code)->update([
            'quantity' => $orderProduct->quantity_ordered - 1,
        ]);

        $this->assertFalse(
            OrderService::canFulfill($order, $warehouse->code)
        );
    }

    public function testSuccessfulCanFulfill(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->create();

        OrderProduct::factory(1)
            ->create(['order_id' => $order->id]);

        /** @var OrderProduct $orderProduct */
        $orderProduct = $order->orderProducts->first();

        $orderProduct->product->inventory($warehouse->code)->update([
            'quantity' => $orderProduct->quantity_ordered,
        ]);

        $this->assertTrue(
            OrderService::canFulfill($order, $warehouse->code)
        );
    }
}

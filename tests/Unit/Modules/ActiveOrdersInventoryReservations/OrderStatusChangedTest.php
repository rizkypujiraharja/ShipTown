<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;
use App\User;
use Tests\TestCase;

class OrderStatusChangedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');

        ActiveOrdersInventoryReservationsServiceProvider::enableModule();
    }

    /** @test */
    public function test_if_releases_quantity_when_status_changed(): void
    {
        // prepare database
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        Configuration::updateOrCreate([], ['warehouse_id' => $warehouse->getKey()]);

        /** @var OrderStatus $orderStatusActive */
        $orderStatusActive = OrderStatus::factory()->create(['order_active' => true]);

        /** @var OrderStatus $orderStatusClosed */
        $orderStatusClosed = OrderStatus::factory()->create(['order_active' => false]);

        // doing something
        $order = Order::factory()->create();

        $orderProduct = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        // assert something
        $order->update(['is_active' => true, 'status_code' => $orderStatusActive->code]);
        $this->assertDatabaseHas('inventory_reservations', ['custom_uuid' => ReservationsService::getUuid($orderProduct)]);

        $order->update(['is_active' => false, 'status_code' => $orderStatusClosed->code]);
        $this->assertDatabaseMissing('inventory_reservations', ['custom_uuid' => ReservationsService::getUuid($orderProduct)]);

        //        $order->update(['is_active' => true, 'status_code' => $orderStatusActive->code]);
        //        $this->assertDatabaseHas('inventory_reservations', ['custom_uuid' => ReservationsService::getUuid($orderProduct)]);
    }
}

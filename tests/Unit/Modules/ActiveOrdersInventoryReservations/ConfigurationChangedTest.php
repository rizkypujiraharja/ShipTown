<?php

namespace Tests\Unit\Modules\ActiveOrdersInventoryReservations;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Warehouse;
use App\Modules\ActiveOrdersInventoryReservations\src\ActiveOrdersInventoryReservationsServiceProvider;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;
use App\Modules\ActiveOrdersInventoryReservations\src\Services\ReservationsService;
use App\User;
use Tests\TestCase;

class ConfigurationChangedTest extends TestCase
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
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $inventory1 = Inventory::find($product->getKey(), $warehouse1->getKey());
        $inventory2 = Inventory::find($product->getKey(), $warehouse2->getKey());

        $config = Configuration::updateOrCreate([], ['warehouse_id' => $warehouse1->getKey()]);

        /** @var OrderStatus $orderStatusActive */
        $orderStatusActive = OrderStatus::factory()->create(['order_active' => true]);

        // doing something
        $order = Order::factory()->create(['is_active' => true, 'status_code' => $orderStatusActive->code]);

        $orderProduct = OrderProduct::factory()->create([
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory1->getKey(),
            'custom_uuid' => ReservationsService::getUuid($orderProduct),
        ]);

        $config->update(['warehouse_id' => $warehouse2->getKey()]);

        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory2->getKey(),
            'custom_uuid' => ReservationsService::getUuid($orderProduct),
        ]);
    }

    /** @test */
    public function test_if_works_when_alias_is_used(): void
    {
        // prepare database
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        $product = Product::factory()->create();

        ProductAlias::create([
            'product_id' => $product->getKey(),
            'alias' => 'test_alias',
        ]);

        $inventory1 = Inventory::find($product->getKey(), $warehouse1->getKey());
        $inventory2 = Inventory::find($product->getKey(), $warehouse2->getKey());

        $config = Configuration::updateOrCreate([], ['warehouse_id' => $warehouse1->getKey()]);

        /** @var OrderStatus $orderStatusActive */
        $orderStatusActive = OrderStatus::factory()->create(['order_active' => true]);

        // doing something
        $order = Order::factory()->create(['is_active' => true, 'status_code' => $orderStatusActive->code]);

        $orderProduct = OrderProduct::factory()->create([
            'sku_ordered' => 'test_alias',
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
        ]);

        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory1->getKey(),
            'custom_uuid' => ReservationsService::getUuid($orderProduct),
        ]);

        $config->update(['warehouse_id' => $warehouse2->getKey()]);

        $this->assertDatabaseHas('inventory_reservations', [
            'inventory_id' => $inventory2->getKey(),
            'custom_uuid' => ReservationsService::getUuid($orderProduct),
        ]);
    }
}

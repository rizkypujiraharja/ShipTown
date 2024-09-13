<?php

namespace Tests\Unit\Modules\Maintenance\Jobs;

use App\Jobs\DispatchEveryHourEventJobs;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Tests\TestCase;

class RecalculateProductQuantityJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        Product::query()->forceDelete();
        Order::query()->forceDelete();
        OrderProduct::query()->forceDelete();

        $order = Order::factory()
            ->create(['status_code' => 'paid']);

        OrderProduct::factory()
            ->create([
                'order_id' => $order->id,
            ]);

        Product::query()->update([
            'quantity' => 0,
        ]);

        DispatchEveryHourEventJobs::dispatchSync();

        $this->assertEquals(
            Product::query()->sum('quantity'),
            Inventory::query()->sum('quantity')
        );
    }
}

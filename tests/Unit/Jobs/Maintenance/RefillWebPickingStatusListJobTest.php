<?php

namespace Tests\Unit\Jobs\Maintenance;

use App\Models\AutoStatusPickingConfiguration;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider;
use App\Modules\AutoStatusPicking\src\Jobs\RefillPickingIfEmptyJob;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Tests\TestCase;

class RefillWebPickingStatusListJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        AutoStatusPickingServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Product::query()->forceDelete();
        OrderProduct::query()->forceDelete();
        Order::query()->forceDelete();

        OrderStatus::factory()->create(['code' => 'paid']);

        Product::factory()->count(30)->create();

        Order::factory()->count(150)
            ->create(['status_code' => 'paid'])
            ->each(function (Order $order) {
                OrderProduct::factory()
                    ->count(rand(1, 5))
                    ->create(['order_id' => $order->id]);
            });

        RefillPickingIfEmptyJob::dispatch();

        $this->assertEquals(
            AutoStatusPickingConfiguration::firstOrCreate([], [])->max_batch_size,
            AutoStatusPickingConfiguration::firstOrCreate([], [])->current_count_with_status,
        );
    }
}

<?php

namespace Tests\Unit\Modules\OrderTotals;

use App\Events\EveryMinuteEvent;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductTotal;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\Modules\OrderTotals\src\Jobs\EnsureAllRecordsExistsJob;
use App\Modules\OrderTotals\src\Jobs\EnsureCorrectTotalsJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EnsureCorrectTotalsJobTest extends TestCase
{
    public function test_if_dispatches_job(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        Bus::fake(EnsureCorrectTotalsJob::class);

        EveryMinuteEvent::dispatch();

        Bus::assertDispatched(EnsureCorrectTotalsJob::class);
    }

    public function test_if_updates_totals(): void
    {
        InventoryReservationsEventServiceProviderBase::enableModule();

        $order = Order::factory()->create(['order_placed_at' => now()]);

        // Create Order with order product
        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create(['order_id' => $order->getKey()]);
        $orderProduct = $orderProduct->refresh();

        // create order without any orderProduct
        Order::factory()->create(['order_placed_at' => now()]);

        OrderProductTotal::query()->forceDelete();

        ray()->showQueries();

        EnsureAllRecordsExistsJob::dispatchSync(now()->subHour(), now()->addDay());
        EnsureCorrectTotalsJob::dispatch();

        ray(OrderProduct::query()->get()->toArray());

        $this->assertDatabaseCount('orders_products_totals', 2);

        $this->assertDatabaseHas('orders_products_totals', ['order_id' => $orderProduct->order_id]);
        $this->assertDatabaseHas('orders_products_totals', ['count' => 1]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_ordered' => $orderProduct->quantity_ordered]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_split' => $orderProduct->quantity_split]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_picked' => $orderProduct->quantity_picked]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_skipped_picking' => $orderProduct->quantity_skipped_picking]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_not_picked' => $orderProduct->quantity_not_picked]);
        $this->assertDatabaseHas('orders_products_totals', ['quantity_shipped' => $orderProduct->quantity_shipped]);
    }
}

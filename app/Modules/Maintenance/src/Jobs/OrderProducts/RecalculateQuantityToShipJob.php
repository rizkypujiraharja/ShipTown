<?php

namespace App\Modules\Maintenance\src\Jobs\OrderProducts;

use App\Models\OrderProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateQuantityToShipJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $records = OrderProduct::query()
            ->whereRaw('quantity_to_ship != quantity_ordered - quantity_shipped')
            ->latest()
            // for performance purposes
            ->limit(1000);

        $records->each(function (OrderProduct $orderProduct) {
            Log::warning('Incorrect quantity to ship detected', [
                'order' => $orderProduct->order->order_number,
                'sku' => $orderProduct->sku_ordered,
            ]);

            $orderProduct->log('Incorrect quantity to ship detected')
                // quantity_to_ship is recalculated on model save
                ->save();
        });

        info('RecalculateQuantityToShipJob finished', ['record_recalculated' => $records->count()]);
    }
}

<?php

namespace App\Modules\Maintenance\src\Jobs\OrderProducts;

use App\Models\OrderProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateQuantityToPickJob implements ShouldQueue
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
            ->whereRaw('(quantity_to_pick) <> (quantity_ordered - quantity_picked - quantity_skipped_picking)')
            ->latest('updated_at')
            ->limit(500);

        $records->each(function (OrderProduct $orderProduct) {
            Log::warning('Incorrect quantity to pick detected', [
                'order' => $orderProduct->order->order_number,
                'sku' => $orderProduct->sku_ordered,
            ]);

            $orderProduct->log('Incorrect quantity to pick detected')
                // quantity_to_pick is recalculated on model save
                ->save();
        });

        info('RecalculateQuantityToPickJob finished', ['record_recalculated' => $records->count()]);
    }
}

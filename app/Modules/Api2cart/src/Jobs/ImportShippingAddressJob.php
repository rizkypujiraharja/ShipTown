<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Models\Order;
use App\Modules\Api2cart\src\Models\Api2cartOrderImports;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportShippingAddressJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $order_id;

    /**
     * Create a new job instance.
     */
    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::whereId($this->order_id)->first();

        if (! $order) {
            return;
        }

        $order_import = Api2cartOrderImports::query()
            ->where(['order_number' => $order->order_number])
            ->latest()
            ->first();

        if (! $order_import) {
            return;
        }

        OrderService::updateOrCreateShippingAddress($order, $order_import->extractShippingAddressAttributes());
        OrderService::updateOrCreateBillingAddress($order, $order_import->extractBillingAddressAttributes());
    }
}

<?php

namespace App\Modules\OrderStatus\src\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class EnsureCorrectIsActiveAndIsOnHoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Order::query()
            ->leftJoin('orders_statuses', 'orders.status_code', '=', 'orders_statuses.code')
            ->whereRaw('orders_statuses.order_on_hold != orders.is_on_hold')
            ->orWhereRaw('orders_statuses.order_active != orders.is_active')
            ->update([
                'orders.is_on_hold' => DB::raw('orders_statuses.order_on_hold'),
                'orders.is_active' => DB::raw('orders_statuses.order_active'),
            ]);
    }
}

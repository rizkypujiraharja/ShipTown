<?php

namespace App\Modules\OrderStatus\src\Jobs;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOrdersIsOnHoldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private OrderStatus $orderStatus;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OrderStatus $orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Order::where([
            'status_code' => $this->orderStatus->code,
            'is_on_hold' => ! $this->orderStatus->order_on_hold,
        ])
            ->each(function (Order $order) {
                $order->update(['is_on_hold' => $this->orderStatus->order_on_hold]);
            });
    }
}

<?php

namespace App\Modules\AutoStatusPicking\src\Jobs;

use App\Models\AutoStatusPickingConfiguration;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefillPickingMissingStockJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    private $configuration;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->configuration = AutoStatusPickingConfiguration::firstOrCreate([], []);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $currentOrdersInProcessCount = $this->configuration->current_count_with_status;

        logger('Refilling "picking" status (warehouse stock)', [
            'current_order_count_with_status' => $this->configuration->current_count_with_status,
            'max_batch_size' => $this->configuration->max_batch_size,
        ]);

        if ($this->configuration->current_count_with_status >= $this->configuration->max_batch_size) {
            return;
        }

        $requiredCount = $this->configuration->required_count;

        $orders = Order::whereStatusCode('paid')
            ->orderBy('order_placed_at')
            ->get();

        foreach ($orders as $order) {
            if (OrderService::canNotFulfill($order, 100)) {
                info('RefillPickingMissingStockJob', ['order_number' => $order->order_number]);
                $order->update(['status_code' => 'picking']);
                $requiredCount--;
            }
            if ($requiredCount <= 0) {
                break;
            }
        }
    }
}

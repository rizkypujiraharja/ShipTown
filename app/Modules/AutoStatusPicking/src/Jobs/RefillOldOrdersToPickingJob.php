<?php

namespace App\Modules\AutoStatusPicking\src\Jobs;

use App\Models\AutoStatusPickingConfiguration;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefillOldOrdersToPickingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var AutoStatusPickingConfiguration
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

        info('Refilling "picking" status (old orders)', [
            'currently_in_process' => $this->configuration->current_count_with_status,
            'max_daily_allowed' => $this->configuration->max_batch_size,
            'ordersRequiredCount' => $this->configuration->required_count,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->configuration->required_count <= 0) {
            return;
        }

        $orders = Order::whereStatusCode('paid')
            ->where('order_placed_at', '<', Carbon::now()->subDays($this->configuration->max_order_age))
            ->orderBy('order_placed_at')
            ->limit($this->configuration->required_count)
            ->get();

        foreach ($orders as $order) {
            $order->log('Outdated order, status_code => "picking"');
            $order->update(['status_code' => 'picking']);
        }
    }
}

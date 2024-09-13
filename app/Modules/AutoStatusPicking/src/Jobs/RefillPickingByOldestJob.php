<?php

namespace App\Modules\AutoStatusPicking\src\Jobs;

use App\Models\AutoStatusPickingConfiguration;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefillPickingByOldestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        logger('Refilling "picking" status', [
            'max_batch_size' => $this->configuration->max_batch_size,
            'currently_in_process' => $this->configuration->current_count_with_status,
        ]);

        if ($this->configuration->required_count <= 0) {
            return;
        }

        Order::whereStatusCode('paid')
            ->orderBy('order_placed_at')
            ->limit($this->configuration->required_count)
            ->get()
            ->each(function (Order $order) {
                $order->log('Refilling picking');
                $order->update(['status_code' => 'picking']);
            });
    }
}

<?php

namespace App\Modules\AutoPilot\src\Jobs;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClearPackerIdJob implements ShouldQueue
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
        $maxHoursOfInactivity = 12;

        $records = Order::whereNull('packed_at')
            ->whereNotNull('packer_user_id')
            ->where('updated_at', '<', Carbon::now()->subHours($maxHoursOfInactivity))
            ->get();

        $records->each(function (Order $order) {
            $order->log('Clearing packer assignment')
                ->update(['packer_user_id' => null]);
        });

        info('ClearPackerIdJob finished', ['record_recalculated' => $records->count()]);
    }
}

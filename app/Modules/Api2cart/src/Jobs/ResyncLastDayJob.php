<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Models\Api2cartConnection;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResyncLastDayJob implements ShouldQueue
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
        Api2cartConnection::all()
            ->each(function (Api2cartConnection $connection) {
                $last_synced_modified_at = Carbon::createFromTimeString($connection->last_synced_modified_at)
                    ->subDay();

                $connection->last_synced_modified_at = $last_synced_modified_at;
                $connection->save();
            });
    }
}

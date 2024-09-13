<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Models\Api2cartVariant;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncVariantsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     *
     * @throws Exception
     */
    public function handle(): void
    {
        Api2cartVariant::query()
            ->where(['is_in_sync' => false])
            ->chunkById(10, function ($variants) {
                foreach ($variants as $variant) {
                    try {
                        SyncVariant::dispatchSync($variant);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                }
            });
    }
}

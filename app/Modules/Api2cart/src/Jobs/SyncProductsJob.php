<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Models\Api2cartSimpleProduct;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductsJob implements ShouldQueue
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
        Api2cartSimpleProduct::query()
            ->where(['is_in_sync' => false])
            ->inRandomOrder()
            ->chunk(10, function ($products) {
                foreach ($products as $product) {
                    try {
                        SyncProduct::dispatchSync($product);
                    } catch (Exception $exception) {
                        report($exception);
                    }
                }
            });
    }
}

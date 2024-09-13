<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Models\Api2cartConnection;
use App\Modules\Api2cart\src\Models\Api2cartVariant;
use App\Modules\Api2cart\src\Services\Api2cartService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchVariantsInfoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     *
     * @throws GuzzleException
     */
    public function handle(): void
    {
        Api2cartConnection::query()
            ->get()
            ->each(function (Api2cartConnection $conn) {
                Api2cartVariant::query()
                    ->where(['api2cart_connection_id' => $conn->id])
                    ->whereRaw('(last_fetched_at IS NULL OR last_fetched_data IS NULL)')
                    ->chunkById(5, function ($chunk) {
                        $chunk->each(function (Api2cartVariant $variant) {
                            $product_now = Api2cartService::getVariantInfoByID(
                                $variant->api2cartConnection,
                                $variant->api2cart_product_id
                            );

                            $variant->last_fetched_data = $product_now;
                            $variant->save();
                        });
                    });
            });
    }
}

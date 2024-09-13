<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Api\RequestResponse;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use App\Modules\Api2cart\src\Services\Api2cartService;
use App\Modules\Api2cart\src\Transformers\ProductTransformer;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SyncProductJob.
 */
class SyncVariant implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Api2cartProductLink $product_link;

    /**
     * Create a new job instance.
     */
    public function __construct(Api2cartProductLink $api2cartProductLink)
    {
        $this->product_link = $api2cartProductLink;
    }

    /**
     * Execute the job.
     *
     *
     * @throws GuzzleException
     */
    public function handle(): void
    {
        if ($this->product_link->api2cart_product_type != 'variant') {
            return;
        }

        $response = Api2cartService::updateVariant(
            $this->product_link->api2cartConnection->bridge_api_key,
            ProductTransformer::toApi2cartPayload($this->product_link),
        );

        $this->product_link->update([
            'last_pushed_at' => now(),
            'last_pushed_response' => $response->asArray(),
        ]);

        switch ($response->getReturnCode()) {
            case RequestResponse::RETURN_CODE_MODEL_NOT_FOUND:
                // product not found
                $this->product_link->update([
                    'api2cart_product_type' => null,
                    'api2cart_product_id' => null,
                ]);
                break;
            case RequestResponse::RETURN_CODE_OK:
                $this->product_link->update([
                    'last_fetched_data' => null,
                    'is_in_sync' => true,
                ]);
                break;
        }
    }
}

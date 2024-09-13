<?php

namespace App\Modules\Api2cart\src\Jobs;

use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use App\Modules\Api2cart\src\Services\Api2cartService;
use App\Modules\Api2cart\src\Transformers\ProductTransformer;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMissingTypeAndIdJob implements ShouldQueue
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
     * @throws Exception
     */
    public function handle(): void
    {
        DB::statement('UPDATE modules_api2cart_product_links SET is_in_sync = 0 WHERE is_in_sync IS NULL');

        $collection = Api2cartProductLink::query()
            ->whereNull('api2cart_product_id')
            ->inRandomOrder()
            ->limit(100)
            ->get();

        $collection->each(function (Api2cartProductLink $link) {
            try {
                $this->updateTypeAndIdOrCreate($link);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                report($e);
            }
        });
    }

    /**
     * @throws Exception|GuzzleException
     */
    private function updateTypeAndIdOrCreate(Api2cartProductLink $link): void
    {
        $typeAndId = Api2cartService::getProductTypeAndId(
            $link->api2cartConnection->bridge_api_key,
            $link->product->sku
        );

        Api2cartProductLink::query()->where([
            'api2cart_connection_id' => $link->api2cart_connection_id,
            'api2cart_product_id' => $typeAndId['id'],
        ])
            ->delete();

        $link->update([
            'api2cart_product_type' => $typeAndId['type'],
            'api2cart_product_id' => $typeAndId['id'],
        ]);

        // if product not found by previous search, create new
        if ($link->api2cart_product_id === null) {
            $response = Api2cartService::createSimpleProduct(
                $link->api2cartConnection->bridge_api_key,
                ProductTransformer::toApi2cartPayload($link)
            );

            if ($response->isNotSuccess()) {
                throw new Exception(implode(' ', [
                    'API2CART Failed to create simple product -',
                    $response->getReturnCode(),
                    $response->getReturnMessage(),
                ]));
            }

            $link->update([
                'api2cart_product_type' => 'simple',
                'api2cart_product_id' => data_get($response->asArray(), 'result.product_id'),
            ]);
        }
    }
}

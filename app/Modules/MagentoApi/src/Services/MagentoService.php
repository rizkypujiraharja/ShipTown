<?php

namespace App\Modules\MagentoApi\src\Services;

use App\Modules\Magento2MSI\src\Api\Client;
use App\Modules\Magento2MSI\src\Api\MagentoApi;
use App\Modules\MagentoApi\src\Exceptions\UnauthorizedException;
use App\Modules\MagentoApi\src\Models\MagentoProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class MagentoService
{
    public static function api(): MagentoApi
    {
        return new MagentoApi;
    }

    public static function fetchSpecialPrices(MagentoProduct $magentoProduct): void
    {
        $connection = $magentoProduct->magentoConnection;

        $response = Client::post($connection->api_access_token, $connection->base_url.'/rest/all/V1/products/special-price-information', [
            'skus' => Arr::wrap($magentoProduct->product->sku),
        ]);

        if ($response === null || $response->failed()) {
            Log::error('Failed to fetch sale prices for product '.$magentoProduct->product->sku);

            return;
        }

        $specialPrices = collect($response->json())
            ->filter(function ($apiSpecialPriceRecord) use ($magentoProduct) {
                return $apiSpecialPriceRecord['store_id'] == $magentoProduct->magentoConnection->magento_store_id;
            });

        // magento sometimes randomly returns multiple special prices for the same store,
        // so we need to filter them out but only one is valid
        // randomizing result will match it sometimes, normally 3 special prices are returned,
        // so we will have statistically 1/3 chance to get the correct one,
        // it's a quick hack, but it works
        $specialPrices = $specialPrices->shuffle();

        $specialPrice = $specialPrices->first();

        if ($specialPrice) {
            $magentoProduct->magento_sale_price = $specialPrice['price'];
            $magentoProduct->magento_sale_price_start_date = $specialPrice['price_from'];
            $magentoProduct->magento_sale_price_end_date = $specialPrice['price_to'];
        }

        $magentoProduct->exists_in_magento = true;
        $magentoProduct->special_prices_fetched_at = now();
        $magentoProduct->special_prices_raw_import = $response->json();
        $magentoProduct->save();
    }

    /**
     * @throws UnauthorizedException
     */
    public static function fetchBasePrices(MagentoProduct $magentoProduct): void
    {
        $connection = $magentoProduct->magentoConnection;

        $response = Client::post($connection->api_access_token, $connection->base_url.'/rest/all/V1/products/base-prices-information', [
            'skus' => Arr::wrap($magentoProduct->product->sku),
        ]);

        if ($response->unauthorized()) {
            throw new UnauthorizedException;
        }

        if ($response === null || $response->failed()) {
            Log::error('Failed to fetch base prices for product '.$magentoProduct->product->sku);

            return;
        }

        $magentoProduct->exists_in_magento = true;
        $magentoProduct->base_prices_fetched_at = now();
        $magentoProduct->base_prices_raw_import = $response->json();

        collect($response->json())
            ->filter(function ($item) use ($magentoProduct) {
                return $item['store_id'] === $magentoProduct->magentoConnection->magento_store_id;
            })
            ->each(function ($item) use ($magentoProduct) {
                $magentoProduct->magento_price = $item['price'];
            });

        $magentoProduct->save();
    }
}

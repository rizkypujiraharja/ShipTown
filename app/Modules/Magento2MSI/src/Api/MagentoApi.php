<?php

namespace App\Modules\Magento2MSI\src\Api;

use App\Modules\Magento2MSI\src\Models\Magento2msiConnection;
use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

class MagentoApi
{
    public static function postInventoryBulkProductSourceAssign(Magento2msiConnection $connection, array $productSkus, array $sourceCodes): ?Response
    {
        return Client::post($connection->api_access_token, $connection->base_url.'/rest/all/V1/inventory/bulk-product-source-assign', [
            'skus' => $productSkus,
            'sourceCodes' => $sourceCodes,
        ]);
    }

    public static function getModules(Magento2msiConnection $connection): ?Response
    {
        return Client::get($connection->api_access_token, $connection->base_url.'/rest/all/V1/modules', [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            //                            [
                            //                                'field' => '',
                            //                                'value' => '',
                            //                                'condition_type' => 'in'
                            //                            ]
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function getInventorySources(Magento2msiConnection $connection): ?Response
    {
        return Client::get($connection->api_access_token, $connection->base_url.'/rest/all/V1/inventory/sources', [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            //                            [
                            //                                'field' => '',
                            //                                'value' => '',
                            //                                'condition_type' => 'in'
                            //                            ]
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function getOrders($token, $parameters = []): ?Response
    {
        return Client::get($token, '/rest/all/V1/orders', $parameters);
    }

    public static function postProducts($token, $sku, $name): ?Response
    {
        return Client::post($token, '/rest/all/V1/products', [
            'products' => [
                [
                    'sku' => $sku,
                    'name' => $name,
                ],
            ],
        ]);
    }

    public static function postProductsSpecialPrice($baseUrl, $token, $store_id, $sku, $price, $price_from, $price_to): ?Response
    {
        return Client::post($token, $baseUrl.'/rest/all/V1/products/special-price', [
            'prices' => [
                [
                    'sku' => $sku,
                    'store_id' => $store_id,
                    'price' => $price,
                    'price_from' => $price_from,
                    'price_to' => $price_to,
                ],
            ],
        ]);
    }

    public static function postProductsSpecialPriceArray($baseUrl, $token, array $special_prices): ?Response
    {
        return Client::post($token, $baseUrl.'/rest/all/V1/products/special-price', [
            'prices' => $special_prices,
        ]);
    }

    public static function postProductsSpecialPriceInformation($token, $sku): ?Response
    {
        return Client::post($token, 'https://guineys.ie/rest/all/V1/products/special-price-information', [
            'skus' => Arr::wrap($sku),
        ]);
    }

    public static function postProductsBasePricesInformation($token, $sku): ?Response
    {
        return Client::post($token, '/rest/all/V1/products/base-prices-information', [
            'skus' => Arr::wrap($sku),
        ]);
    }

    public static function putStockItems($token, $sku, $params): ?Response
    {
        return Client::put($token, '/rest/all/V1/products/'.$sku.'/stockItems/0', [
            'stockItem' => $params,
        ]);
    }

    public static function getStockItems($token, $sku): ?Response
    {
        return Client::get($token, '/rest/all/V1/stockItems/'.$sku);
    }

    public static function getProducts(Magento2msiConnection $connection, $skuList): ?Response
    {
        $skus = collect($skuList)->implode(',');

        return Client::get($connection->api_access_token, $connection->base_url.'/rest/all/V1/products', [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => $skus,
                                'condition_type' => 'in',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public static function getInventorySourceItems(Magento2msiConnection $connection, $skuList): ?Response
    {
        $skus = collect($skuList)->implode(',');

        return Client::get($connection->api_access_token, $connection->base_url.'/rest/all/V1/inventory/source-items', [
            'searchCriteria' => [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => $skus,
                                'condition_type' => 'in',
                            ],
                        ],
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'source_code',
                                'value' => $connection->magento_source_code,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    //    public static function postInventorySourceItems($token, $sku, $storeCode, $quantity): ?Response
    public static function postInventorySourceItems(Magento2msiConnection $connection, $sourceItems): ?Response
    {
        return Client::post($connection->api_access_token, $connection->base_url.'/rest/all/V1/inventory/source-items', [
            'sourceItems' =>
//                [
                $sourceItems,
            //                [
            //                    'source_code' => $storeCode,
            //                    'sku' => $sku,
            //                    'quantity' => $quantity,
            //                    'status' => 1,
            //                ]
            //            ],
        ]);
    }

    public static function postProductsBasePrices(MagentoConnection $connection, string $sku, float $price, int $store_id): ?Response
    {
        return Client::post($connection->api_access_token, $connection->base_url.'/rest/all/V1/products/base-prices', [
            'prices' => [
                [
                    'sku' => $sku,
                    'price' => $price,
                    'store_id' => $store_id,
                ],
            ],
        ]);
    }

    public static function postProductsBaseBulkPrices(string $api_access_token, string $base_url, array $attributes): ?Response
    {
        return Client::post($api_access_token, $base_url.'/rest/all/V1/products/base-prices', [
            'prices' => $attributes,
        ]);
    }

    public static function fetchBasePricesBulk(string $api_access_token, string $base_url, array $attributes): ?Response
    {
        return Client::post($api_access_token, $base_url.'/rest/all/V1/products/base-prices-information', [
            'skus' => $attributes,
        ]);
    }

    public static function fetchSpecialPricesBulk(string $api_access_token, string $base_url, array $attributes): ?Response
    {
        return Client::post($api_access_token, $base_url.'/rest/all/V1/products/special-price-information', [
            'skus' => $attributes,
        ]);
    }
}

<?php

namespace Tests\External\Rmsapi;

use App\Modules\Rmsapi\src\Api\Client as RmsapiClient;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use GuzzleHttp\Exception\GuzzleException;
use Tests\TestCase;

class RmsapiConnectionTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function test_if_fetches_products(): void
    {
        $connection = RmsapiConnection::factory()->create([
            'location_id' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
            'url' => env('TEST_RMSAPI_URL'),
            'username' => env('TEST_RMSAPI_USERNAME'),
            'password' => env('TEST_RMSAPI_PASSWORD'),
        ]);

        $response = RmsapiClient::GET($connection, 'api/products');

        $this->assertTrue($response->isSuccess());
    }
}

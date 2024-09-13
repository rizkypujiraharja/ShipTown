<?php

namespace Tests\External\Rmsapi;

use Tests\TestCase;

class EnvVariablesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     *
     * @throws \Exception
     */
    public function test_if_variables_set(): void
    {
        $this->assertNotEmpty(env('TEST_RMSAPI_WAREHOUSE_CODE'), 'TEST_RMSAPI_WAREHOUSE_CODE .env not set');
        $this->assertNotEmpty(env('TEST_RMSAPI_URL'), 'TEST_RMSAPI_URL .env not set');
        $this->assertNotEmpty(env('TEST_RMSAPI_USERNAME'), 'TEST_RMSAPI_USERNAME .env not set');
        $this->assertNotEmpty(env('TEST_RMSAPI_PASSWORD'), 'TEST_RMSAPI_PASSWORD .env not set');
    }
}

<?php

namespace Tests\External\Api2cart;

use Tests\TestCase;

class EnvTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_if_store_key_is_configured(): void
    {
        $this->assertNotEmpty(config('api2cart.api2cart_test_store_key'));
    }
}

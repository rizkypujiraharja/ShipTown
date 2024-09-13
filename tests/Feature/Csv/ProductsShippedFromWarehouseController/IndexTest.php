<?php

namespace Tests\Feature\Csv\ProductsShippedFromWarehouseController;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Csv\ProductsShippedFromWarehouseController
 */
class IndexTest extends TestCase
{
    /**
     * @test
     */
    public function index_returns_an_ok_response(): void
    {
        $user = \App\User::factory()->create();

        $response = $this->actingAs($user)->get(route('warehouse_shipped.csv'));

        $response->assertOk();
    }
}

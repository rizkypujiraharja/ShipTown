<?php

namespace Tests\Feature\Api\Warehouses;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $response = $this->get(route('api.warehouses.index', ['include' => 'tags']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'code', 'tags'],
            ],
        ]);
    }
}

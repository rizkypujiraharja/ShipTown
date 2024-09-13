<?php

namespace Tests\Feature\Api\Configurations;

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
        $response = $this->get(route('api.configurations.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'business_name',
            ],
        ]);
    }
}

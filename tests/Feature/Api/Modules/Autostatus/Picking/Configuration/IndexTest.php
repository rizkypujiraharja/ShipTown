<?php

namespace Tests\Feature\Api\Modules\Autostatus\Picking\Configuration;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $response = $this->get(route('modules.autostatus.picking.configuration.index'));

        $response->assertSuccessful();
    }
}

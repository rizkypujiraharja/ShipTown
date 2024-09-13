<?php

namespace Tests\Feature\Api\Modules\Printnode\Clients;

use App\Modules\PrintNode\src\Models\Client;
use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $client = Client::factory()->make();

        $response = $this->post(route('api.modules.printnode.clients.store', $client));

        $response->assertStatus(400);
    }
}

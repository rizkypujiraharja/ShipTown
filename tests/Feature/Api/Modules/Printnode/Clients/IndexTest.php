<?php

namespace Tests\Feature\Api\Modules\Printnode\Clients;

use App\Modules\PrintNode\src\Models\Client;
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
        Client::factory()->create();

        $response = $this->get(route('api.modules.printnode.clients.index'));

        $response->assertSuccessful();
    }
}

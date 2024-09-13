<?php

namespace Tests\Feature\Api\Modules\Printnode\Printers;

use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create()->assignRole('user');
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function test_index_call_returns_ok(): void
    {
        $response = $this->get('api/modules/printnode/printers');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [],
            ],
        ]);
    }
}

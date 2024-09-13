<?php

namespace Tests\Feature\Api\NavigationMenu;

use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_returns_ok(): void
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $response = $this->get(route('api.navigation-menu.index'));

        $response->assertSuccessful();
    }
}

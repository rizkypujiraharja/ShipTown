<?php

namespace Tests\Feature\Api\NavigationMenu;

use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private function simulationTest($body = null)
    {
        if (is_null($body)) {
            $body = [
                'name' => 'testing',
                'url' => 'testing',
                'group' => 'picklist',
            ];
        }

        $response = $this->post(route('api.navigation-menu.store'), $body);

        return $response;
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->simulationTest();

        $response->assertSuccessful();
    }

    public function test_store_call_should_be_loggedin(): void
    {
        $response = $this->simulationTest();

        $response->assertRedirect(route('login'));
    }

    public function test_store_call_should_loggedin_as_admin(): void
    {
        Passport::actingAs(
            User::factory()->create()
        );

        $response = $this->simulationTest();

        $response->assertForbidden();
    }

    public function test_all_field_is_required(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->simulationTest([]);

        $response->assertSessionHasErrors([
            'name',
            'url',
            'group',
        ]);
    }

    public function test_group_not_packlist_or_picklist(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->simulationTest([
            'name' => 'tes',
            'url' => 'tes',
            'group' => 'tes',
        ]);

        $response->assertSessionHasErrors([
            'group',
        ]);
    }
}

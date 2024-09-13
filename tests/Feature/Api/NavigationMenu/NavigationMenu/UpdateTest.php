<?php

namespace Tests\Feature\Api\NavigationMenu\NavigationMenu;

use App\Models\NavigationMenu;
use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UpdateTest extends TestCase
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

        $navigationMenu = NavigationMenu::create([
            'name' => 'testing',
            'url' => 'testing',
            'group' => 'picklist',
        ]);

        $response = $this->put(route('api.navigation-menu.update', $navigationMenu), $body);

        return $response;
    }

    /** @test */
    public function test_update_call_returns_ok(): void
    {
        Passport::actingAs(
            User::factory()->admin()->create()
        );

        $response = $this->simulationTest();

        $response->assertSuccessful();
    }

    public function test_update_call_should_be_loggedin(): void
    {
        $response = $this->simulationTest();

        $response->assertRedirect(route('login'));
    }

    public function test_update_call_should_loggedin_as_admin(): void
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

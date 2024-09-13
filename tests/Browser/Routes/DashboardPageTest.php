<?php

namespace Tests\Browser\Routes;

use App\Models\Configuration;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class DashboardPageTest extends DuskTestCase
{
    private string $uri = '/dashboard';

    protected function setUp(): void
    {
        parent::setUp();

        Configuration::query()->update(['ecommerce_connected' => true]);
    }

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        $this->basicUserAccessTest($this->uri, true);
        $this->basicAdminAccessTest($this->uri, true);
        $this->basicGuestAccessTest($this->uri);
    }

    /**
     * @throws Throwable
     */
    public function testUserAccess(): void
    {
        $this->browse(function (Browser $browser) {
            /** @var User $user */
            $user = User::factory()->create();
            $user->assignRole('user');

            $browser->disableFitOnFailure();
            $browser->loginAs($user);
            $browser->visit($this->uri);
            $browser->pause(300);
            $browser->assertSee('Orders - Packed');
            $browser->assertSee('Orders - Active');
            $browser->assertSee('Active Orders By Age');
        });
    }
}

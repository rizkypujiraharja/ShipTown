<?php

namespace Tests\Browser\Routes;

use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class FulfillmentStatisticsPageTest extends DuskTestCase
{
    private string $uri = '/fulfillment-statistics';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->disableFitOnFailure();
            $browser->loginAs($user);
            $browser->visit($this->uri);
            $browser->assertPathIs($this->uri);
            $browser->assertSourceMissing('Server Error');

            $this->markTestIncomplete('This test has not been implemented yet.');
        });
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
}

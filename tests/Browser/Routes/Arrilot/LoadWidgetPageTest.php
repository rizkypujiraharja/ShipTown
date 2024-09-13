<?php

namespace Tests\Browser\Routes\Arrilot;

use App\User;
use Exception;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class LoadWidgetPageTest extends DuskTestCase
{
    private string $uri = '';

    public function testIncomplete(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    //    /**
    //     * @throws Exception
    //     */
    //    protected function setUp(): void
    //    {
    //        if (empty($this->uri)) {
    //            throw new Exception('Please set the $uri property in your test class.');
    //        }
    //
    //        parent::setUp();
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testUserAccess()
    //    {
    //        $this->browse(function (Browser $browser) {
    //            /** @var User $user */
    //            $user = User::factory()->create();
    //            $user->assignRole('user');
    //
    //            $browser->disableFitOnFailure()
    //                ->loginAs($user)
    //                ->visit($this->uri)
    //                ->pause(300)
    //                ->assertRouteIs($this->uri)
    //                ->assertSourceMissing('snotify-error');
    //        });
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testAdminAccess()
    //    {
    //        $this->browse(function (Browser $browser) {
    //            /** @var User $admin */
    //            $admin = User::factory()->create();
    //            $admin->assignRole('admin');
    //
    //            $browser->disableFitOnFailure()
    //                ->loginAs($admin)
    //                ->visit($this->uri)
    //                ->pause(300)
    //                ->assertRouteIs($this->uri)
    //                ->assertSourceMissing('snotify-error');
    //        });
    //    }
    //
    //    /**
    //     * @throws Throwable
    //     */
    //    public function testGuestAccess()
    //    {
    //        $this->browse(function (Browser $browser) {
    //            $browser->disableFitOnFailure()
    //                ->logout()
    //                ->visit($this->uri)
    //                ->assertRouteIs('login')
    //                ->assertSee('Login');
    //        });
    //    }
}

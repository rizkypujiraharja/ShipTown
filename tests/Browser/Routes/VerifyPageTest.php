<?php

namespace Tests\Browser\Routes;

use App\Models\Configuration;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class VerifyPageTest extends DuskTestCase
{
    private string $uri = '/verify';

    protected function setUp(): void
    {
        parent::setUp();

        Configuration::query()->updateOrCreate([], ['disable_2fa' => false]);
    }

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->two_factor_code = '123456';
        $user->save();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->disableFitOnFailure();
            $browser->loginAs($user);
            $browser->deleteCookie('device_guid');
            $browser->visit($this->uri);
            $browser->assertPathIs($this->uri);
            $browser->type('#two_factor_code', '123456');
            $browser->press('Verify');
            $browser->assertPathIsNot('/login');
            $browser->assertPathIsNot('/register');
            $browser->assertHasCookie('device_guid');
            $browser->assertSourceMissing('Server Error');
        });
    }

    /**
     * @throws Throwable
     */
    public function test_if_guest_not_allowed(): void
    {
        User::query()->delete();

        $this->browse(function (Browser $browser) {
            $browser->disableFitOnFailure();
            $browser->visit($this->uri);
            $browser->assertPathIs('/register');
        });

        User::factory()->create();

        $this->browse(function (Browser $browser) {
            $browser->disableFitOnFailure();
            $browser->visit($this->uri);
            $browser->assertPathIs('/login');
        });
    }
}

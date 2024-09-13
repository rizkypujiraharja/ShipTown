<?php

namespace Tests\Browser\Routes;

use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class RegisterPageTest extends DuskTestCase
{
    private string $uri = '/register';

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        User::query()->delete();

        $this->browse(function (Browser $browser) {
            $browser->disableFitOnFailure();
            $browser->visit($this->uri);
            $browser->assertPathIs($this->uri);
        });
    }
}

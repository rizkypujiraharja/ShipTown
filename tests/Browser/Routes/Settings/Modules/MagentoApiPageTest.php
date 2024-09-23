<?php

namespace Tests\Browser\Routes\Settings\Modules;

use App\Modules\MagentoApi\src\EventServiceProviderBase;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class MagentoApiPageTest extends DuskTestCase
{
    private string $uri = '/settings/modules/magento-api';

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        EventServiceProviderBase::enableModule();

        $this->basicAdminAccessTest($this->uri, true);
        $this->basicUserAccessTest($this->uri, false);
        $this->basicGuestAccessTest($this->uri);
    }

    /**
     * @throws Throwable
     */
    public function testPage(): void
    {
        EventServiceProviderBase::enableModule();

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit($this->uri)
                ->assertSee('Magento Api Configurations');
        });
    }
}

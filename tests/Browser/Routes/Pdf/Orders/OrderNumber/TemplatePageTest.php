<?php

namespace Tests\Browser\Routes\Pdf\Orders\OrderNumber;

use App\Models\Order;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class TemplatePageTest extends DuskTestCase
{
    private string $uri = '/pdf/orders/{order_number}/{template}';

    protected function setUp(): void
    {
        parent::setUp();

        $order = Order::factory()->create();
        $this->uri = str_replace('{order_number}', $order->id, $this->uri);

        $template = 'address_label';
        $this->uri = str_replace('{template}', $template, $this->uri);
    }

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
            // $browser->assertSee('');
            $browser->assertSourceMissing('Server Error');
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

<?php

namespace Tests\Browser\Routes;

use App\Models\Order;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class OrdersPageTest extends DuskTestCase
{
    private string $uri = '/orders';

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
    public function testBasicScenarios(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);

        $this->browse(function (Browser $browser) use ($user, $order) {
            $browser->disableFitOnFailure();
            $browser->loginAs($user);

            $browser->visit($this->uri);
            $browser->waitForText($order->order_number);
            $browser->assertSee($order->status_code);

            /** @var Order $order */
            $order = Order::factory()->create();

            $browser->type('@barcode-input-field', $order->order_number);
            $browser->keys('@barcode-input-field', '{enter}');
            $browser->waitForText($order->order_number);
            $browser->assertSee($order->status_code);
        });
    }
}

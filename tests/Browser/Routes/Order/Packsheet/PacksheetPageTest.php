<?php

namespace Tests\Browser\Routes\Order\Packsheet;

use App\Models\Order;
use App\Models\OrderProduct;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class PacksheetPageTest extends DuskTestCase
{
    private string $uri = '/order/packsheet';

    /**
     * @throws Throwable
     */
    public function testBasics(): void
    {
        $order = Order::factory()->create();

        $newUri = implode('', [$this->uri, '/', $order->getKey()]);

        $this->basicUserAccessTest($newUri, true);
        $this->basicAdminAccessTest($newUri, true);
        $this->basicGuestAccessTest($newUri);
    }

    /**
     * @throws Throwable
     */
    public function testBasicScenarios(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create();
        OrderProduct::factory()->count(5)->create(['order_id' => $order->getKey()]);

        $newUri = implode('', [$this->uri, '/', $order->getKey()]);

        $this->browse(function (Browser $browser) use ($newUri, $order) {
            /** @var User $user */
            $user = User::factory()->create();
            $user->assignRole('user');

            $browser->disableFitOnFailure();

            $browser->loginAs($user);
            $browser->visit($newUri);
            $browser->waitUntilMissingText('No products found', 1);
            $browser->assertSourceMissing('snotify-error');
            $browser->pause(500);
            $browser->assertSee($order->order_number);

            /** @var OrderProduct $randomOrderProduct */
            $randomOrderProduct = $order->orderProducts()->inRandomOrder()->first();

            $browser->type('@barcode-input-field', $randomOrderProduct->sku_ordered);
            $browser->keys('@barcode-input-field', '{enter}');
            $browser->waitForText('1 x shipped');
        });
    }
}

<?php

namespace Tests\Browser\Routes\Autopilot;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class PacklistPageTest extends DuskTestCase
{
    private string $uri = '/autopilot/packlist';

    /**
     * @throws Throwable
     */
    public function testAccessLevels(): void
    {
        $this->basicUserAccessTest($this->uri, true);
        $this->basicAdminAccessTest($this->uri, true);
        $this->basicGuestAccessTest($this->uri);
    }

    /**
     * @throws Throwable
     */
    public function testPressStartAutopilot(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');

        /** @var OrderStatus $orderStatus */
        $orderStatus = OrderStatus::factory()->create(['name' => 'paid', 'code' => 'paid', 'order_active' => true]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => $orderStatus->code]);

        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = OrderProduct::factory()->create(['order_id' => $order->getKey()]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        $orderProduct1->product
            ->inventory($user->warehouse->code)
            ->update([
                'quantity' => $orderProduct1->quantity_ordered,
                'quantity_reserved' => 0,
            ]);

        $order->refresh();
        $order->update(['total_paid' => $order->total_order]);
        $order->update(['order_status' => $orderStatus->code]);

        $this->browse(function (Browser $browser) use ($user, $order) {
            $browser->disableFitOnFailure();

            $browser->loginAs($user)
                ->pause($this->shortDelay)->visit($this->uri.'?step=select')
                ->pause($this->shortDelay)->waitForText('Status: paid', 1)
                ->pause($this->shortDelay)->clickLink('Status: paid')
                ->pause($this->shortDelay)->waitForText($order->order_number, 1)
                ->pause($this->shortDelay)->assertSourceMissing('snotify-error');
        });
    }
}

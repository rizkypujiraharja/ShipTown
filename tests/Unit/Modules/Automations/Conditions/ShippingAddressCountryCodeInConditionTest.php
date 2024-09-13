<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\ShippingAddressCountryCodeInCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingAddressCountryCodeInConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_shipping_method_code_equals_validator(): void
    {
        AutomationsServiceProvider::enableModule();

        /** @var Automation $automation */
        $automation = Automation::create([
            'enabled' => true,
            'name' => 'paid to store_pickup',
        ]);

        /** @var Condition $condition */
        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => ShippingAddressCountryCodeInCondition::class,
            'condition_value' => 'PL',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'international',
        ]);

        $address = OrderAddress::factory()->create(['country_code' => 'PL']);

        /** @var Order $order */
        $order = Order::factory()->create([
            'status_code' => 'paid',
            'shipping_method_code' => 'international',
            'shipping_address_id' => $address->getKey(),
        ]);

        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        RunEnabledAutomationsOnSpecificOrderJob::dispatch($order->getKey());

        $order = $order->refresh();

        $this->assertEquals('international', $order->status_code);
    }
}

<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShippingMethodCodeEqualsConditionTest extends TestCase
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
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        /** @var Condition $condition */
        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => ShippingMethodCodeEqualsCondition::class,
            'condition_value' => 'store_pickup',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'store_pickup',
        ]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid', 'shipping_method_code' => 'store_pickup']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        RunEnabledAutomationsOnSpecificOrderJob::dispatch($order->getKey());

        $order = $order->refresh();

        $this->assertEquals('store_pickup', $order->status_code);
    }
}

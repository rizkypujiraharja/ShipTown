<?php

namespace Tests\Unit\Modules\Automations;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_basic_automation(): void
    {
        AutomationsServiceProvider::enableModule();

        /** @var Automation $automation */
        $automation = Automation::create([
            'enabled' => false,
            'name' => 'Paid to Picking',
        ]);

        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'new_status',
        ]);

        $automation->enabled = true;
        $automation->save();

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        RunEnabledAutomationsOnSpecificOrderJob::dispatch($order->id);

        $order = $order->refresh();

        $this->assertEquals('new_status', $order->status_code);
    }
}

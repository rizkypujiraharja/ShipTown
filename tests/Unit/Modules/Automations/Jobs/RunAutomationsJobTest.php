<?php

namespace Tests\Unit\Modules\Automations\Jobs;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\AutomationsServiceProvider;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class RunAutomationsJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        ray(Order::all()->toArray());

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);

        /** @var Order $order */
        $order = Order::factory()->create(['status_code' => 'paid']);
        OrderProduct::factory()->create(['order_id' => $order->getKey()]);
    }

    /**
     * A basic feature test example.
     */
    public function test_basic_automation(): void
    {
        $this->assertCount(5, Order::query()->where(['status_code' => 'paid'])->get());

        RunEnabledAutomationsJob::dispatchSync();

        $this->assertCount(5, Order::query()->where(['status_code' => 'new_status'])->get());
    }
}

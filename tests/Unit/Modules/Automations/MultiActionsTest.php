<?php

namespace Tests\Unit\Modules\Automations;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class MultiActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::factory()->create();

        /** @var Automation $automation1 */
        $automation1 = Automation::create([
            'enabled' => true,
            'name' => 'status1 to status2',
        ]);

        Condition::create([
            'automation_id' => $automation1->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => $orderProduct->order->status_code,
        ]);

        Action::create([
            'automation_id' => $automation1->getKey(),
            'action_class' => SetLabelTemplateAction::class,
            'action_value' => 'label_template',
        ]);

        Action::create([
            'automation_id' => $automation1->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'new_status',
        ]);
    }

    public function testExample(): void
    {
        RunEnabledAutomationsJob::dispatchSync();

        $this->assertCount(1, Order::query()->where(['status_code' => 'new_status'])->get());

        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Unit\Modules\Automations;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class MultiAutomationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Automation $automation1 */
        $automation1 = Automation::create([
            'enabled' => true,
            'name' => 'status1 to status2',
        ]);

        /** @var Automation $automation2 */
        $automation2 = Automation::create([
            'enabled' => true,
            'name' => 'status2 to status3',
        ]);

        Condition::create([
            'automation_id' => $automation1->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'status1',
        ]);

        Action::create([
            'automation_id' => $automation1->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'status2',
        ]);

        Condition::create([
            'automation_id' => $automation2->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'status2',
        ]);

        Action::create([
            'automation_id' => $automation2->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'status3',
        ]);

        OrderStatus::create(['name' => 'status1', 'code' => 'status1']);
        OrderStatus::create(['name' => 'status2', 'code' => 'status2']);
        OrderStatus::create(['name' => 'status3', 'code' => 'status3']);
    }

    public function testExample(): void
    {
        OrderProduct::factory()->count(10)->create();

        Order::query()->update(['status_code' => 'status1']);

        RunEnabledAutomationsJob::dispatchSync();

        ray(Order::query()->pluck('status_code')->toArray());

        $this->assertCount(10, Order::query()->where(['status_code' => 'status3'])->get());
        $this->assertCount(0, Order::query()->where(['status_code' => 'status2'])->get());
        $this->assertCount(0, Order::query()->where(['status_code' => 'status1'])->get());

        $this->assertTrue(true);
    }
}

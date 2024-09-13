<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\OrderNumberEqualsCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class OrderNumberEqualsConditionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        OrderStatus::query()->forceDelete();
        Automation::query()->forceDelete();

        ray()->clearAll();

        $automation = Automation::factory()->create();

        Condition::factory()->create([
            'automation_id' => $automation->getKey(),
            'condition_class' => OrderNumberEqualsCondition::class,
            'condition_value' => '123456',
        ]);

        Action::factory()->create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'new_status_code',
        ]);

        $automation->update(['enabled' => true]);
    }

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        OrderStatus::factory()->create(['code' => 'active', 'order_active' => true]);

        $order1 = Order::factory()->create(['order_number' => '000000', 'status_code' => 'active']);
        OrderProduct::factory()->create(['order_id' => $order1->getKey()]);

        $order2 = Order::factory()->create(['order_number' => '123456', 'status_code' => 'active']);
        OrderProduct::factory()->create(['order_id' => $order2->getKey()]);

        RunEnabledAutomationsJob::dispatch();

        $this->assertDatabaseHas('orders', ['status_code' => 'new_status_code']);
    }
}

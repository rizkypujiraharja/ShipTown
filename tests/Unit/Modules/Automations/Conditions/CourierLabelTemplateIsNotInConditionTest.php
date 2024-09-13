<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Modules\Automations\src\Actions\Order\SetLabelTemplateAction;
use App\Modules\Automations\src\Conditions\Order\CourierLabelTemplateIsNotInCondition;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Tests\TestCase;

class CourierLabelTemplateIsNotInConditionTest extends TestCase
{
    public function test_functionality(): void
    {
        $automation = Automation::factory()->create();

        Condition::factory()->create([
            'automation_id' => $automation->getKey(),
            'condition_class' => CourierLabelTemplateIsNotInCondition::class,
            'condition_value' => 'next_day',
        ]);

        Action::factory()->create([
            'automation_id' => $automation->getKey(),
            'action_class' => SetLabelTemplateAction::class,
            'action_value' => 'next_day',
        ]);

        $automation->update(['enabled' => true]);

        $order1 = Order::factory()->create(['label_template' => '']);
        OrderProduct::factory()->create(['order_id' => $order1->getKey()]);

        $order2 = Order::factory()->create(['label_template' => '  ']);
        OrderProduct::factory()->create(['order_id' => $order2->getKey()]);

        $order3 = Order::factory()->create(['label_template' => '3day_service']);
        OrderProduct::factory()->create(['order_id' => $order3->getKey()]);

        $order4 = Order::factory()->create(['label_template' => 'next_day']);
        OrderProduct::factory()->create(['order_id' => $order4->getKey()]);

        ray()->showQueries();

        RunEnabledAutomationsJob::dispatch();

        $this->assertEquals(4, Order::query()->where(['label_template' => 'next_day'])->count());
    }
}

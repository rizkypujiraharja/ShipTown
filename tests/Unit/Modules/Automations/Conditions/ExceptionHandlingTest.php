<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use App\Modules\Automations\tests\Feature\Conditions\ExceptionTestCondition;
use Mockery\Exception;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        OrderStatus::query()->forceDelete();
        Automation::query()->forceDelete();

        $automation = Automation::factory()->create();

        Condition::factory()->create([
            'automation_id' => $automation->getKey(),
            'condition_class' => ExceptionTestCondition::class,
            'condition_value' => '',
        ]);

        $automation->update(['enabled' => true]);
    }

    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        try {
            Order::factory()->create();

            RunEnabledAutomationsJob::dispatch();
        } catch (Exception $exception) {
            $this->fail('Exceptions: '.$exception->getMessage());
        }

        $this->assertTrue(true, 'We just make sure no exceptions returned');
    }
}

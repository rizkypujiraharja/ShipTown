<?php

namespace Tests\Unit\Modules\Automations\Conditions;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use App\Modules\Automations\src\Services\AutomationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllConditionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        OrderStatus::query()->forceDelete();
        Automation::query()->forceDelete();

        $automation = Automation::factory()->create();

        AutomationService::availableConditions()
            ->pluck('class')
            ->each(function ($condition_class) use ($automation) {
                // add each condition to automation
                Condition::factory()->create([
                    'automation_id' => $automation->getKey(),
                    'condition_class' => $condition_class,
                    'condition_value' => '',
                ]);
            });

        $automation->update(['enabled' => true]);
    }

    /**
     * A basic feature test example.
     */
    public function test_if_thorws_any_exception(): void
    {
        try {
            Order::factory()->create();

            RunEnabledAutomationsJob::dispatch();
        } catch (Exception $exception) {
            ray($exception);
            $this->fail('Exceptions occurred when running all conditions');
        }

        $this->assertTrue(true, 'We just make sure no exceptions returned');
    }
}

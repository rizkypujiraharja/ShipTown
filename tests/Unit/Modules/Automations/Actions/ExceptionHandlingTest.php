<?php

namespace Tests\Unit\Modules\Automations\Actions;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsJob;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\tests\Feature\Actions\ExceptionTestAction;
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

        Action::factory()->create([
            'automation_id' => $automation->getKey(),
            'action_class' => ExceptionTestAction::class,
            'action_value' => '',
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

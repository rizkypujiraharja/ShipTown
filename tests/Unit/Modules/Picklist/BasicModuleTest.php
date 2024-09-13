<?php

namespace Tests\Unit\Modules\Picklist;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Pick;
use App\Modules\Picklist\src\PicklistServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PicklistServiceProvider::enableModule();
    }

    /** @test */
    public function testBasicFunctionality(): void
    {
        $pick = Pick::factory()->create();

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => $pick->quantity_picked]);

        $pick->delete();

        $this->assertDatabaseHas('picks', ['is_distributed' => true, 'quantity_distributed' => 0, 'deleted_at' => $pick->deleted_at]);

    }

    /** @test */
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

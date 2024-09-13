<?php

namespace Tests\Unit\Modules\Inventory;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\EveryWeekEvent;
use App\Modules\Inventory\src\InventoryServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryServiceProvider::enableModule();
    }

    /** @test */
    public function testBasicFunctionality(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /** @test */
    public function testIfNoErrorsDuringEvents(): void
    {
        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();
        EveryWeekEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

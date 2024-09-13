<?php

namespace Tests\Unit\Modules\InventoryGroups;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\EveryWeekEvent;
use App\Modules\InventoryGroups\src\InventoryGroupsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InventoryGroupsServiceProvider::enableModule();
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

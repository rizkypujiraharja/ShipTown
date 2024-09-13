<?php

namespace Tests\Unit\Modules\MagentoApi;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Modules\MagentoApi\src\EventServiceProviderBase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EventServiceProviderBase::enableModule();
    }

    /** @test */
    public function test_module_basic_functionality(): void
    {
        if (env('TEST_MODULES_MAGENTO_BASE_URL') === null) {
            $this->markTestSkipped('Magento base url not set');
        }

        $this->assertTrue(true, 'Most basic test... to be continued');
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

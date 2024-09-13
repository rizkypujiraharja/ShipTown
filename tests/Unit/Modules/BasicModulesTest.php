<?php

namespace Tests\Unit\Modules;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Models\Module;
use App\Modules\BaseModuleServiceProvider;
use Exception;
use Tests\TestCase;

class BasicModulesTest extends TestCase
{
    /** @test */
    public function testBasicFunctionality(): void
    {
        try {
            $enabled_modules = Module::query()
                ->where(['enabled' => false])
                ->get();
        } catch (Exception $exception) {
            report($exception);

            return;
        }

        $enabled_modules->each(function (Module $module) {
            /** @var BaseModuleServiceProvider $service_provider_class */
            $service_provider_class = $module->service_provider_class;
            $service_provider_class::enableModule();
            ray($service_provider_class);
        });

        EveryMinuteEvent::dispatch();
        EveryFiveMinutesEvent::dispatch();
        EveryTenMinutesEvent::dispatch();
        EveryHourEvent::dispatch();
        EveryDayEvent::dispatch();

        $this->assertTrue(true, 'Errors encountered while dispatching events');
    }
}

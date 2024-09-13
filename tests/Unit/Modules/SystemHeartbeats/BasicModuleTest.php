<?php

namespace Tests\Unit\Modules\SystemHeartbeats;

use App\Events\EveryDayEvent;
use App\Events\EveryTenMinutesEvent;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Models\Heartbeat;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use App\Modules\SystemHeartbeats\src\Listeners\EveryDayEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryFiveMinutesEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryHourEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryMinuteEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryTenMinutesEventListener;
use App\Modules\SystemHeartbeats\src\SystemHeartbeatsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SystemHeartbeatsServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Heartbeat::query()->forceDelete();
    }

    /** @test */
    public function test_EveryMinuteEvent_heartbeat(): void
    {
        DispatchEveryMinuteEventJob::dispatch();

        $this->assertDatabaseHas('heartbeats', [
            'code' => EveryMinuteEventListener::class,
        ]);
    }

    /** @test */
    public function test_FiveMinutesEvent_heartbeat(): void
    {
        DispatchEveryFiveMinutesEventJob::dispatch();

        $this->assertDatabaseHas('heartbeats', [
            'code' => EveryFiveMinutesEventListener::class,
        ]);
    }

    /** @test */
    public function test_TenMinutesEvent_heartbeat(): void
    {
        EveryTenMinutesEvent::dispatch();

        $this->assertDatabaseHas('heartbeats', [
            'code' => EveryTenMinutesEventListener::class,
        ]);
    }

    /** @test */
    public function test_hourlyEvent_heartbeat(): void
    {
        DispatchEveryHourEventJobs::dispatch();

        $this->assertDatabaseHas('heartbeats', [
            'code' => EveryHourEventListener::class,
        ]);
    }

    /** @test */
    public function test_dailyEvent_heartbeat(): void
    {
        EveryDayEvent::dispatch();

        $this->assertDatabaseHas('heartbeats', [
            'code' => EveryDayEventListener::class,
        ]);
    }
}

<?php

namespace Tests\Unit\Modules\AutoPilot;

use App\Jobs\DispatchEveryHourEventJobs;
use App\Modules\AutoPilot\src\AutoPilotServiceProvider;
use App\Modules\AutoPilot\src\Jobs\ClearPackerIdJob;
use App\Modules\InventoryReservations\src\EventServiceProviderBase as InventoryReservationsEventServiceProviderBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_if_dispatches_jobs(): void
    {
        AutoPilotServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Bus::fake();

        $job = new DispatchEveryHourEventJobs;
        $job->handle();

        Bus::assertDispatched(ClearPackerIdJob::class);
    }
}

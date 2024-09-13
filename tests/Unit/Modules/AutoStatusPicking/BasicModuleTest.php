<?php

namespace Tests\Unit\Modules\AutoStatusPicking;

use App\Jobs\DispatchEveryHourEventJobs;
use App\Modules\AutoStatusPicking\src\AutoStatusPickingServiceProvider;
use App\Modules\AutoStatusPicking\src\Jobs\RefillPickingIfEmptyJob;
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
    public function test_basic_functionality(): void
    {
        AutoStatusPickingServiceProvider::enableModule();
        InventoryReservationsEventServiceProviderBase::enableModule();

        Bus::fake();

        $job = new DispatchEveryHourEventJobs;
        $job->handle();

        Bus::assertDispatched(RefillPickingIfEmptyJob::class);
    }
}

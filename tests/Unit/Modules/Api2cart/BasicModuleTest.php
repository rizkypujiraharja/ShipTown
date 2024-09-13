<?php

namespace Tests\Unit\Modules\Api2cart;

use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Modules\Api2cart\src\Api2cartServiceProvider;
use App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testIfDispatchesJobs(): void
    {
        Api2cartServiceProvider::enableModule();

        Bus::fake();

        $job = new DispatchEveryFiveMinutesEventJob;
        $job->handle();

        Bus::assertDispatched(DispatchImportOrdersJobs::class);
    }
}

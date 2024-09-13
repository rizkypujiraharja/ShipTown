<?php

namespace Tests\Unit\Modules\Rmsapi;

use App\Modules\Rmsapi\src\Jobs\ImportAllJob;
use App\Modules\Rmsapi\src\Jobs\ImportShippingsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\RmsapiModuleServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class JobsDispatchTests extends TestCase
{
    use RefreshDatabase;

    public function test_if_dispatches_import_jobs(): void
    {
        Bus::fake();

        RmsapiModuleServiceProvider::enableModule();

        RmsapiConnection::factory()->create();

        Bus::assertDispatched(ImportAllJob::class);
        Bus::assertDispatched(ImportShippingsJob::class);
    }
}

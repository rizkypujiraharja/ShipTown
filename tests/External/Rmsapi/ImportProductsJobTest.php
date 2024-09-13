<?php

namespace Tests\External\Rmsapi;

use App\Modules\Rmsapi\src\Jobs\ImportProductsJob;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedProductRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ImportProductsJobTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     *
     * @throws \Exception
     */
    public function testIfJobRuns(): void
    {
        Bus::fake();
        Event::fake();

        // we want clean data
        RmsapiConnection::query()->delete();
        RmsapiProductImport::query()->delete();

        $connection = RmsapiConnection::factory()->create([
            'location_id' => env('TEST_RMSAPI_WAREHOUSE_CODE'),
            'url' => env('TEST_RMSAPI_URL'),
            'username' => env('TEST_RMSAPI_USERNAME'),
            'password' => env('TEST_RMSAPI_PASSWORD'),
        ]);

        $job = new ImportProductsJob($connection->getKey());

        $job->handle();

        $this->assertTrue(RmsapiProductImport::query()->exists(), 'No imports have been made');
        Bus::assertDispatched(ProcessImportedProductRecordsJob::class);
    }
}

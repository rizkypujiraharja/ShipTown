<?php

namespace Tests\Unit\Modules\Rmsapi;

use App\Modules\Rmsapi\src\Jobs\ProcessImportedProductRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Tests\TestCase;

class ProcessImportedProductRecordsJobTest extends TestCase
{
    public function test_handle(): void
    {
        RmsapiProductImport::factory(10)->create();

        ProcessImportedProductRecordsJob::dispatch();

        $this->assertEmpty(RmsapiProductImport::query()->whereNull('processed_at')->get());
    }
}

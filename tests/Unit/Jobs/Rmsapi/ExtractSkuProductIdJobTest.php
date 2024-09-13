<?php

namespace Tests\Unit\Jobs\Rmsapi;

use App\Models\Warehouse;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedProductRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Tests\TestCase;

class ExtractSkuProductIdJobTest extends TestCase
{
    public function testIfAllSkuArePopulated(): void
    {
        Warehouse::factory()->create();

        RmsapiProductImport::factory()->count(1)->create([
            'product_id' => null,
            'processed_at' => null,
        ]);

        // do
        foreach (RmsapiConnection::all() as $rmsapiConnection) {
            ProcessImportedProductRecordsJob::dispatch($rmsapiConnection->id);
        }

        // assert
        $this->assertFalse(
            RmsapiProductImport::query()
                ->whereNotNull('processed_at')
                ->whereNull('sku')
                ->exists(),
            'sku column is not populated'
        );

        $this->assertFalse(
            RmsapiProductImport::query()
                ->whereNotNull('processed_at')
                ->whereNull('product_id')
                ->exists(),
            'product_id column is not populated'
        );
    }
}

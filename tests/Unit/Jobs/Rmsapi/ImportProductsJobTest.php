<?php

namespace Tests\Unit\Jobs\Rmsapi;

use App\Modules\Rmsapi\src\Jobs\ImportProductsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiProductImport;
use Tests\TestCase;

class ImportProductsJobTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testBatchSaving(): void
    {

        // cleanup data
        RmsapiConnection::query()->delete();
        RmsapiProductImport::query()->delete();

        // prepare defaults
        $rmsapiConnection = RmsapiConnection::factory()->create();
        $productCount = random_int(10, 100);
        $products = RmsapiProductImport::factory()->count($productCount)->make();

        $productsCollection = collect($products)
            ->map(function ($product) {
                return $product->raw_import;
            })
            ->sortBy('db_change_stamp');

        // do the job
        $job = new ImportProductsJob($rmsapiConnection->id);
        $job->saveImportedProducts($productsCollection->toArray());

        // test if right data was saved
        $this->assertEquals(
            $productCount,
            RmsapiProductImport::query()
                ->where('connection_id', '=', $rmsapiConnection->id)
                ->where('batch_uuid', '=', $job->batch_uuid)
                ->whereNull('processed_at')
                ->count(),
            'Did not save all records'
        );

        $this->assertEquals(
            RmsapiConnection::find($rmsapiConnection->id)->products_last_timestamp,
            $productsCollection->last()['db_change_stamp'],
            'products_last_timestamp not updated correctly'
        );
    }
}

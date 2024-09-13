<?php

namespace Tests\Unit\Modules\Rmsapi;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedSalesRecordsJob;
use App\Modules\Rmsapi\src\Jobs\UpdateImportedSalesRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use App\Modules\Rmsapi\src\Models\RmsapiSaleImport;
use Tests\TestCase;

class ProcessImportedSalesRecordsJobTest extends TestCase
{
    public function testIfDuplicatesNotAllowed(): void
    {
        // prepare
        /** @var RmsapiSaleImport $saleRecord */
        $saleRecord = RmsapiSaleImport::factory()->create();

        UpdateImportedSalesRecordsJob::dispatchSync();

        // execute
        ProcessImportedSalesRecordsJob::dispatchSync();

        $saleRecord->update([
            'reserved_at' => null,
            'processed_at' => null,
        ]);

        ProcessImportedSalesRecordsJob::dispatchSync();

        ray(InventoryMovement::query()->get()->toArray());

        // assert
        $product = Product::findBySKU($saleRecord->sku);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->getKey(),
            'description' => 'rms_sale',
            'quantity_delta' => $saleRecord->quantity,
        ]);

        $this->assertDatabaseCount('inventory_movements', 1);

        $this->assertDatabaseMissing('modules_rmsapi_sales_imports', [
            'id' => $saleRecord->id,
            'processed_at' => null,
        ]);
    }

    public function testIfImportsSale(): void
    {
        // prepare
        /** @var RmsapiSaleImport $saleRecord */
        $saleRecord = RmsapiSaleImport::factory()->create();

        UpdateImportedSalesRecordsJob::dispatchSync();

        // execute
        ProcessImportedSalesRecordsJob::dispatchSync();

        // assert
        $product = Product::findBySKU($saleRecord->sku);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->getKey(),
            'description' => 'rms_sale',
            'quantity_delta' => $saleRecord->quantity,
        ]);

        $this->assertDatabaseMissing('modules_rmsapi_sales_imports', [
            'id' => $saleRecord->id,
            'processed_at' => null,
        ]);
    }

    public function testIfSkipsOrderProductShipmentsTransactions(): void
    {
        /** @var RmsapiConnection $rmsapiConnection */
        $rmsapiConnection = RmsapiConnection::factory()->create();

        /** @var RmsapiSaleImport $saleRecord */
        $saleRecord = RmsapiSaleImport::create([
            'connection_id' => $rmsapiConnection->id,
            'comment' => 'PM_OrderProductShipment_1234567890',
        ]);

        ProcessImportedSalesRecordsJob::dispatchSync();

        $this->assertDatabaseHas('modules_rmsapi_sales_imports', [
            'id' => $saleRecord->id,
            'processed_at' => null,
        ]);
    }
}

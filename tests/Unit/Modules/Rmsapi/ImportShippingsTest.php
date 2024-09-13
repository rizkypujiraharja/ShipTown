<?php

namespace Tests\Unit\Modules\Rmsapi;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTotal;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Rmsapi\src\Jobs\ImportShippingsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Tests\TestCase;

class ImportShippingsTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $connection = RmsapiConnection::factory()->create(['location_id' => $warehouse->code]);

        $job = new ImportShippingsJob($connection->getKey());
        $records = [
            [
                'DBTimeStamp' => 1,
                'ID' => 1,
                'ShippingDateCreated' => '2020-01-01 00:00:00',
                'TransactionNumber' => '1',
                'TransactionComment' => 'test comment',
                'ItemLookupCode' => $product->sku,
                'ItemDescription' => 'test description',
                'TransactionEntryQuantity' => 1,
                'TransactionEntryPrice' => 1,
                'TransactionEntryComment' => 'test transaction entry comment',
                'ShippingCharge' => 1,
                'ShippingCarrierName' => 'PM',
                'ShippingServiceName' => 'PM',
                'ShippingNotes' => 'test shipping note',
                'Name' => 'test name',
                'Company' => 'test company',
                'Address' => 'test address',
                'Address2' => 'test address2',
                'City' => 'test city',
                'State' => 'test state',
                'Zip' => 'test zip',
                'Country' => 'test country',
                'PhoneNumber' => 'test phone number',
                'EmailAddress' => 'test@email.com',
                'CarrierName' => 'PM',
                'ServiceName' => 'PM',
            ],
        ];

        $job->importShippingRecords($records);

        $orderProduct = OrderProduct::first();

        $uuid = 'rmsapi_shipping_import-order_product_id-'.$orderProduct->id;

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity_delta' => 1,
            'description' => 'rmsapi_shipping_import',
            'custom_unique_reference_id' => $uuid,
        ]);

        // intend to import the same record again and expect no new inventory movement to be created
        $job->importShippingRecords($records);

        $this->assertDatabaseCount('inventory_movements', 1);

        ray([
            'orders' => Order::all()->toArray(),
            'inventory' => Inventory::all()->toArray(),
            'inventory_totals' => InventoryTotal::all()->toArray(),
            'inventory_movements' => InventoryMovement::all()->toArray(),
        ]);
    }
}

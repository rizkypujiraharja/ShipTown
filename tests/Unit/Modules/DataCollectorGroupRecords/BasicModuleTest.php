<?php

namespace Tests\Unit\Modules\DataCollectorGroupRecords;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use App\Modules\DataCollectorGroupRecords\src\DataCollectorGroupRecordsServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ray()->clearAll();

        DataCollectorServiceProvider::enableModule();
        DataCollectorGroupRecordsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4001 = Product::factory()->create(['sku' => '4001', 'price' => 10]);
        $this->product4005 = Product::factory()->create(['sku' => '4005', 'price' => 50]);

        $this->product4001->prices()
            ->update([
                'price' => 10,
                'sale_price' => '10',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->product4005->prices()
            ->update([
                'price' => 50,
                'sale_price' => '50',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);
    }

    /** @test */
    public function testExample(): void
    {
        /** @var DataCollection $dataCollection */
        $dataCollection = DataCollection::factory()->create([
            'type' => DataCollectionTransaction::class,
            'warehouse_id' => $this->warehouse->getKey(),
            'warehouse_code' => $this->warehouse->code,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4001->getKey(),
            'inventory_id' => $this->product4001->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 5,
            'unit_full_price' => 10,
            'unit_sold_price' => 10,
            'quantity_scanned' => 1,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 20,
            'unit_full_price' => 50,
            'unit_sold_price' => 50,
            'quantity_scanned' => 2,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 20,
            'unit_full_price' => 50,
            'unit_sold_price' => 25,
            'quantity_scanned' => 1,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 20,
            'unit_full_price' => 50,
            'unit_sold_price' => 25,
            'quantity_scanned' => 1,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_code' => $this->warehouse->code,
            'warehouse_id' => $this->warehouse->getKey(),
            'unit_cost' => 20,
            'unit_full_price' => 50,
            'unit_sold_price' => 25,
            'quantity_scanned' => 1,
            'quantity_requested' => 0,
            'price_source' => 'QUANTITY_DISCOUNT',
            'price_source_id' => 1,
        ]);

        DataCollectorService::recalculate($dataCollection);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(4, DataCollectionRecord::query()->where('data_collection_id', $dataCollection->id)->count());
    }
}

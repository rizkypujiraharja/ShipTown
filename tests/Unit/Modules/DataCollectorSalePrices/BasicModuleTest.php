<?php

namespace Tests\Unit\Modules\DataCollectorSalePrices;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollector\src\Services\DataCollectorService;
use App\Modules\DataCollectorSalePrices\src\DataCollectorSalePricesServiceProvider;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        DataCollectorSalePricesServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4005 = Product::factory()->create(['sku' => '4005', 'price' => 50]);

        $this->product4005->prices()
            ->update([
                'inventory_id' => $this->product4005->inventory()->first()->id,
                'price' => 50,
                'sale_price' => 35.0,
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

        $record = DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4005->getKey(),
            'inventory_id' => $this->product4005->inventory()->first()->id,
            'warehouse_id' => $this->warehouse->getKey(),
            'warehouse_code' => $this->warehouse->code,
            'unit_cost' => 5,
            'unit_full_price' => 50,
            'unit_sold_price' => 50,
            'quantity_scanned' => 2,
            'quantity_requested' => 0,
        ]);

        $record->prices()
            ->associate(ProductPrice::where('product_id', $this->product4005->getKey())->first())
            ->save();

        DataCollectorService::recalculate($dataCollection);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(70, $dataCollection->refresh()->total_sold_price);
    }
}

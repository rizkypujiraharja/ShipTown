<?php

namespace Tests\Unit\Modules\DataCollectorQuantityDiscounts;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\VolumePurchasePriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Tests\TestCase;

class MultibuyPriceDiscountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        QuantityDiscountsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4001 = Product::factory()->create(['sku' => '4001', 'price' => 10]);

        $this->product4001->prices()
            ->update([
                'price' => 25,
                'sale_price' => 25,
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $quantityDiscount = QuantityDiscount::factory()->create([
            'name' => 'Buy 5 or more for €5 each',
            'job_class' => VolumePurchasePriceDiscount::class,
            'configuration' => [
                'multibuy_discount_ranges' => [
                    [
                        'minimum_quantity' => 5,
                        'discounted_price' => 5,
                    ],
                    [
                        'minimum_quantity' => 10,
                        'discounted_price' => 4,
                    ],
                    [
                        'minimum_quantity' => 15,
                        'discounted_price' => 3,
                    ],
                ],
            ],
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4001->getKey(),
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
            'product_id' => $this->product4001->getKey(),
            'inventory_id' => $this->product4001->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 5,
            'unit_full_price' => 25,
            'unit_sold_price' => 25,
            'quantity_scanned' => 7,
            'quantity_requested' => 0,
        ]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(35, $dataCollection->refresh()->total_sold_price);

        $record->update(['quantity_scanned' => 12]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(48, $dataCollection->refresh()->total_sold_price);

        $record->update(['quantity_scanned' => 17]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(51, $dataCollection->refresh()->total_sold_price);
    }
}

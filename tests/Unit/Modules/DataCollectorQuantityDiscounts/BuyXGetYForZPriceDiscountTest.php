<?php

namespace Tests\Unit\Modules\DataCollectorQuantityDiscounts;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Tests\TestCase;

class BuyXGetYForZPriceDiscountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        QuantityDiscountsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4007 = Product::factory()->create(['sku' => '4007', 'price' => 40]);
        $this->product4008 = Product::factory()->create(['sku' => '4008', 'price' => 65]);

        $this->product4007->prices()
            ->update([
                'price' => 40,
                'sale_price' => '25.99',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->product4008->prices()
            ->update([
                'price' => 65,
                'sale_price' => '35.99',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $quantityDiscount = QuantityDiscount::factory()->create([
            'name' => 'Buy 3, get 1 for $10',
            'job_class' => BuyXGetYForZPriceDiscount::class,
            'configuration' => [
                'quantity_full_price' => 3,
                'quantity_discounted' => 1,
                'discounted_price' => 10,
            ],
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4007->getKey(),
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4008->getKey(),
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
            'product_id' => $this->product4007->getKey(),
            'inventory_id' => $this->product4007->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 5,
            'unit_full_price' => 40,
            'unit_sold_price' => 40,
            'quantity_scanned' => 4,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4008->getKey(),
            'inventory_id' => $this->product4008->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 20,
            'unit_full_price' => 65,
            'unit_sold_price' => 65,
            'quantity_scanned' => 2,
            'quantity_requested' => 0,
        ]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(260, $dataCollection->refresh()->total_sold_price);
    }
}

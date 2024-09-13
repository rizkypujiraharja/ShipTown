<?php

namespace Tests\Unit\Modules\DataCollectorQuantityDiscounts;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Tests\TestCase;

class BuyXForYPriceDiscountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        QuantityDiscountsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4002 = Product::factory()->create(['sku' => '4002', 'price' => 30]);
        $this->product4003 = Product::factory()->create(['sku' => '4003', 'price' => 40]);

        $this->product4002->prices()
            ->update([
                'price' => 30,
                'sale_price' => '25.99',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->product4003->prices()
            ->update([
                'price' => 40,
                'sale_price' => '35.99',
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $quantityDiscount = QuantityDiscount::factory()->create([
            'name' => 'Buy 5 for €10 (€2 each)',
            'job_class' => BuyXForYPriceDiscount::class,
            'configuration' => [
                'quantity_required' => 5,
                'discounted_unit_price' => 10,
            ],
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4002->getKey(),
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4003->getKey(),
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
            'product_id' => $this->product4002->getKey(),
            'inventory_id' => $this->product4002->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 5,
            'unit_full_price' => 30,
            'unit_sold_price' => 30,
            'quantity_scanned' => 4,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4003->getKey(),
            'inventory_id' => $this->product4003->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 20,
            'unit_full_price' => 40,
            'unit_sold_price' => 40,
            'quantity_scanned' => 2,
            'quantity_requested' => 0,
        ]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(90, $dataCollection->refresh()->total_sold_price);
    }
}

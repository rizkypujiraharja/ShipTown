<?php

namespace Tests\Unit\Modules\DataCollectorQuantityDiscounts;

use App\Models\DataCollection;
use App\Models\DataCollectionRecord;
use App\Models\DataCollectionTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Tests\TestCase;

class BuyXForYPercentDiscountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DataCollectorServiceProvider::enableModule();
        QuantityDiscountsServiceProvider::enableModule();

        $this->warehouse = Warehouse::factory()->create();

        $this->product4004 = Product::factory()->create(['sku' => '4004', 'price' => 35]);
        $this->product4006 = Product::factory()->create(['sku' => '4006', 'price' => 50]);

        $this->product4004->prices()
            ->update([
                'cost' => 5,
                'price' => 35,
                'sale_price' => 25.99,
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $this->product4006->prices()
            ->update([
                'cost' => 20,
                'price' => 50,
                'sale_price' => 35.99,
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->addDays(7),
            ]);

        $quantityDiscount = QuantityDiscount::factory()->create([
            'name' => 'Buy 5 and get 10% OFF',
            'job_class' => BuyXForYPercentDiscount::class,
            'configuration' => [
                'quantity_required' => 5,
                'discount_percent' => 10,
            ],
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4004->getKey(),
        ]);

        QuantityDiscountsProduct::factory()->create([
            'quantity_discount_id' => $quantityDiscount->id,
            'product_id' => $this->product4006->getKey(),
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
            'product_id' => $this->product4004->getKey(),
            'inventory_id' => $this->product4004->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 5,
            'unit_full_price' => 35,
            'unit_sold_price' => 35,
            'quantity_scanned' => 3,
            'quantity_requested' => 0,
        ]);

        DataCollectionRecord::query()->create([
            'data_collection_id' => $dataCollection->getKey(),
            'product_id' => $this->product4006->getKey(),
            'inventory_id' => $this->product4006->inventory()->first()->id,
            'warehouse_code' => $dataCollection->warehouse_code,
            'warehouse_id' => $dataCollection->warehouse_id,
            'unit_cost' => 20,
            'unit_full_price' => 50,
            'unit_sold_price' => 50,
            'quantity_scanned' => 3,
            'quantity_requested' => 0,
        ]);

        ray($dataCollection->refresh(), $dataCollection->refresh()->records()->get()->toArray());

        $this->assertEquals(234.5, $dataCollection->refresh()->total_sold_price);
    }
}

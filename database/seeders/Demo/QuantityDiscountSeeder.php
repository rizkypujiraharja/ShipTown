<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXForYPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\BuyXGetYForZPriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\VolumePurchasePercentDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Jobs\VolumePurchasePriceDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscountsProduct;
use Illuminate\Database\Seeder;

class QuantityDiscountSeeder extends Seeder
{
    public function run(): void
    {
        $quantityDiscount1 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 2, get 2 half price',
                'job_class' => BuyXGetYForZPercentDiscount::class,
                'configuration' => [
                    'quantity_full_price' => 2,
                    'quantity_discounted' => 2,
                    'discount_percent' => 50,
                ],
            ]);

        $quantityDiscount2 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 3, get 1 for $10',
                'job_class' => BuyXGetYForZPriceDiscount::class,
                'configuration' => [
                    'quantity_full_price' => 3,
                    'quantity_discounted' => 1,
                    'discounted_price' => 10,
                ],
            ]);

        $quantityDiscount3 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 5 and get 10% OFF',
                'job_class' => BuyXForYPercentDiscount::class,
                'configuration' => [
                    'quantity_required' => 5,
                    'discount_percent' => 10,
                ],
            ]);

        $quantityDiscount4 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 5 for €10 (€2 each)',
                'job_class' => BuyXForYPriceDiscount::class,
                'configuration' => [
                    'quantity_required' => 5,
                    'discounted_unit_price' => 10,
                ],
            ]);

        $quantityDiscount5 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 5 for 10% discount, buy 10 for 15% discount',
                'job_class' => VolumePurchasePercentDiscount::class,
                'configuration' => [
                    'multibuy_discount_ranges' => [
                        [
                            'minimum_quantity' => 5,
                            'discount_percent' => 10,
                        ],
                        [
                            'minimum_quantity' => 10,
                            'discount_percent' => 15,
                        ],
                    ],
                ],
            ]);

        $quantityDiscount6 = QuantityDiscount::factory()
            ->create([
                'name' => 'Buy 5 for €3 each, buy 10 for €2 each',
                'job_class' => VolumePurchasePriceDiscount::class,
                'configuration' => [
                    'multibuy_discount_ranges' => [
                        [
                            'minimum_quantity' => 5,
                            'discounted_price' => 3,
                        ],
                        [
                            'minimum_quantity' => 10,
                            'discounted_price' => 2,
                        ],
                    ],
                ],
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount1->id,
                'product_id' => Product::where(['sku' => '4001'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount1->id,
                'product_id' => Product::where(['sku' => '4002'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount2->id,
                'product_id' => Product::where(['sku' => '4003'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount2->id,
                'product_id' => Product::where(['sku' => '4004'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount3->id,
                'product_id' => Product::where(['sku' => '4005'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount3->id,
                'product_id' => Product::where(['sku' => '4006'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount4->id,
                'product_id' => Product::where(['sku' => '4007'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount4->id,
                'product_id' => Product::where(['sku' => '4008'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount5->id,
                'product_id' => Product::where(['sku' => '4009'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount5->id,
                'product_id' => Product::where(['sku' => '40011'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount6->id,
                'product_id' => Product::where(['sku' => '40013'])->first(),
            ]);

        QuantityDiscountsProduct::factory()
            ->create([
                'quantity_discount_id' => $quantityDiscount6->id,
                'product_id' => Product::where(['sku' => '40014'])->first(),
            ]);
    }
}

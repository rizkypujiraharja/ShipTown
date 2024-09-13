<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class ProductsPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()
            ->chunkById(50, function ($productsList) {
                foreach ($productsList as $product) {
                    $randomPrice = rand(1, 100) - (0.05 * rand(0, 1));

                    $salesPrice = $randomPrice * ([1, 1, 2 / 3, 0.5][rand(0, 3)]);
                    $saleStartDate = today()->subDays(rand(10, 30));
                    $salePriceEndDate = today()->subDays(9)->addDays(rand(0, 20));

                    ProductPrice::query()
                        ->where('product_id', '=', $product->getKey())
                        ->update([
                            'cost' => $randomPrice * rand(40, 70) / 100,
                            'price' => $randomPrice,
                            'sale_price' => $salesPrice,
                            'sale_price_start_date' => $saleStartDate,
                            'sale_price_end_date' => $salePriceEndDate,
                        ]);
                }
            });

        Product::skuOrAlias('4001')->first()->prices()
            ->update([
                'price' => 10,
                'sale_price' => 17.99,
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->subDays(7),
            ]);

        Product::skuOrAlias('4002')->first()->prices()
            ->update([
                'price' => 20,
                'sale_price' => 8.99,
                'sale_price_start_date' => now()->subDays(3),
                'sale_price_end_date' => now()->addDays(4),
            ]);

        Product::skuOrAlias('4005')->first()->prices()
            ->update([
                'price' => 50,
                'sale_price' => 17.99,
                'sale_price_start_date' => now()->subDays(14),
                'sale_price_end_date' => now()->subDays(7),
            ]);
    }
}

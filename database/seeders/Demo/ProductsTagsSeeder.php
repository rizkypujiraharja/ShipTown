<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        $products->each(function (Product $product) {
            $product->attachTag('Available Online');
            $product->attachTag('PRODUCT_ID_'.$product->id);
        });
    }
}

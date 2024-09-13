<?php

namespace Database\Seeders\Demo;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAlias;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //        Product::query()->updateOrCreate(['sku' => '1'], ['name' => 'Tennis Racket']);
        //        Product::factory()->create(['sku' => '41', 'name' => 'Tennis Balls 6pk']);
        //        Product::factory()->create(['sku' => '42', 'name' => 'White Tennis Shirt L']);
        //        Product::factory()->create(['sku' => '43', 'name' => 'Equipment Trolley Black']);
        //        Product::factory()->create(['sku' => '44', 'name' => 'Tennis Racket EVO PRO']);
        //
        //        $product = Product::factory()->create(['sku' => '45', 'name' => 'Blue Shorts XL']);
        //        Inventory::query()->where(['product_id' => $product->getKey()])->update(['shelve_location' => 'A1']);

        Product::factory()->create(['sku' => 'X001LE6Q8H', 'name' => 'Eyoyo Clip On Barcode Scanner']);

        //        Product::factory()->create(['sku' => '46', 'name' => 'Silver Tennis Racket']);
        //        Product::factory()->create(['sku' => '47', 'name' => 'Green Cap']);
        //        Product::factory()->create(['sku' => '48', 'name' => 'Ball Mega pack 100']);
        //        Product::factory()->create(['sku' => '49', 'name' => 'EVO PRO 2 Tennis Racket']);
        //
        //        Product::factory()->create(['sku' => '3001', 'name' => 'Test Product - 3001']);
        //        Product::factory()->create(['sku' => '3002', 'name' => 'Test Product - 3002']);
        //        Product::factory()->create(['sku' => '3003', 'name' => 'Test Product - 3003']);
        //        Product::factory()->create(['sku' => '3004', 'name' => 'Test Product - 3004']);
        //        Product::factory()->create(['sku' => '3005', 'name' => 'Test Product - 3005']);

        Product::factory()->create(['sku' => '40011', 'name' => 'Secret Box']);
        Product::factory()->create(['sku' => '40012', 'name' => 'Power Adaptor']);
        Product::factory()->create(['sku' => '40013', 'name' => 'Christmas Snowball']);
        Product::factory()->create(['sku' => '40014', 'name' => 'Gloves Size L']);
        Product::factory()->create(['sku' => '40015', 'name' => 'Buttons 100pk']);

        Product::factory()->create(['sku' => '4001', 'name' => 'T-Shirt Blue']);
        Product::factory()->create(['sku' => '4002', 'name' => 'T-Shirt Brown Grey']);
        Product::factory()->create(['sku' => '4003', 'name' => 'T-Shirt Light Brown']);
        Product::factory()->create(['sku' => '4004', 'name' => 'T-Shirt Light Grey']);
        Product::factory()->create(['sku' => '4005', 'name' => 'T-Shirt Grey']);
        Product::factory()->create(['sku' => '4006', 'name' => 'T-Shirt Black']);
        Product::factory()->create(['sku' => '4007', 'name' => 'T-Shirt Purple']);
        Product::factory()->create(['sku' => '4008', 'name' => 'T-Shirt Green']);
        Product::factory()->create(['sku' => '4009', 'name' => 'T-Shirt ']);

        //        Product::factory()->count(50)->create();
    }

    private function createSkuWithAliases(array $skuList): void
    {
        foreach ($skuList as $sku) {
            if (! Product::query()->where('sku', '=', $sku)->exists()) {
                /** @var Product $product */
                $product = Product::factory()->create(['sku' => $sku]);

                ProductAlias::factory()->create([
                    'product_id' => $product->getKey(),
                    'alias' => $product->sku.'-alias',
                ]);

                ProductAlias::factory()->create([
                    'product_id' => $product->getKey(),
                    'alias' => $product->sku.'a',
                ]);
            }
        }
    }
}

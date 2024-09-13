<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Modules\DpdIreland\src\DpdIrelandServiceProvider;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Database\Seeder;

class DpdIrelandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(env('TEST_DPD_USER'))) {
            return;
        }

        DpdIreland::factory()->create([
            'live' => true,
            'token' => env('TEST_DPD_TOKEN'),
            'user' => env('TEST_DPD_USER'),
            'password' => env('TEST_DPD_PASSWORD'),
        ]);

        DpdIrelandServiceProvider::enableModule();

        OrderStatus::factory()->create([
            'name' => 'test_orders_courier_dpd_ireland',
            'code' => 'test_orders_courier_dpd_ireland',
            'order_active' => true,
            'order_on_hold' => true,
        ]);

        $this->createTestOrder();
    }

    private function createTestOrder(): void
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'John';
        $testAddress->last_name = 'Smith';
        $testAddress->phone = '12345678901';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'IE';
        $testAddress->postcode = 'B661BY';
        $testAddress->address1 = 'DPD Ireland';
        $testAddress->address2 = 'Unit 2B Midland Gateway Bus';
        $testAddress->city = 'Westmeath';
        $testAddress->state_code = 'Kilbeggan';
        $testAddress->email = 'john.smith@dpd.ie';
        $testAddress->save();

        $orders[] = Order::factory()->create([
            'order_number' => 'T100004 - DPD Ireland Test Order',
            'shipping_address_id' => $testAddress->getKey(),
            'status_code' => 'paid',
            'label_template' => 'dpd_irl_next_day',
        ]);

        /** @var Product $product */
        $product = Product::query()->inRandomOrder('45')->first();

        foreach ($orders as $order) {
            OrderProduct::factory()->create([
                'order_id' => $order->getKey(),
                'product_id' => $product->getKey(),
                'quantity_ordered' => 1,
                'price' => $product->price,
                'name_ordered' => $product->name,
                'sku_ordered' => $product->sku,
            ]);
            $order->refresh();

            $order->update(['total_paid' => $order->total_order]);
        }

    }
}

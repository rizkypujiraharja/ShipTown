<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Modules\DpdUk\src\DpdUkServiceProvider;
use App\Modules\DpdUk\src\Models\Connection;
use Illuminate\Database\Seeder;

class DpdUKSeeder extends Seeder
{
    public function run(): void
    {
        if (empty(env('TEST_DPDUK_USERNAME'))) {
            return;
        }

        $collectionAddress = OrderAddress::factory()->create([
            'company' => 'ShipTown',
            'address1' => '58-60 Richmond Road',
            'address2' => 'Twickenham',
            'city' => 'London',
            'postcode' => 'TW1 3BE',
            'country_code' => 'GB',
            'country_name' => 'GB',
        ]);

        Connection::factory()->create([
            'username' => env('TEST_DPDUK_USERNAME'),
            'password' => env('TEST_DPDUK_PASSWORD'),
            'account_number' => env('TEST_DPDUK_ACCNUMBER'),
            'collection_address_id' => $collectionAddress->getKey(),
        ]);

        DpdUKServiceProvider::enableModule();

        OrderStatus::factory()->create([
            'name' => 'test_orders_courier_dpd_uk',
            'code' => 'test_orders_courier_dpd_uk',
            'order_active' => true,
            'order_on_hold' => true,
        ]);

        $this->createTestOrder();
    }

    private function createTestOrder()
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'Artur';
        $testAddress->last_name = 'Hanusek';
        $testAddress->phone = '12345678901';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'GBR';
        $testAddress->country_name = 'United Kingdom';
        $testAddress->postcode = 'BN6 8QQ';
        $testAddress->address1 = '28 Stafford Way';
        $testAddress->address2 = '';
        $testAddress->city = 'Hassocks';
        $testAddress->state_code = '';
        $testAddress->state_name = 'SUSSEX';
        $testAddress->email = fake()->email;

        $testAddress->save();

        $orders[] = Order::factory()->create([
            'shipping_address_id' => $testAddress->id,
            'label_template' => 'dpd_uk_next_day',
            'status_code' => 'test_orders_courier_dpd_uk',
        ]);

        $orders[] = Order::factory()->create([
            'shipping_address_id' => $testAddress->id,
            'label_template' => 'dpd_uk_next_day',
            'status_code' => 'packing',
        ]);

        /** @var Product $product */
        $product = Product::findBySku('45');

        foreach ($orders as $order) {
            OrderProduct::factory()->create([
                'order_id' => $order->getKey(),
                'product_id' => $product->getKey(),
                'quantity_ordered' => 1,
                'price' => $product->price,
                'name_ordered' => $product->name,
                'sku_ordered' => $product->sku,
            ]);
        }

    }
}

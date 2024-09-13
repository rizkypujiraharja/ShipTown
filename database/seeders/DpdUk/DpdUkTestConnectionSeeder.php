<?php

namespace Database\Seeders\DpdUk;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Modules\DpdUk\src\DpdUkServiceProvider;
use App\Modules\DpdUk\src\Models\Connection;
use Illuminate\Database\Seeder;

class DpdUkTestConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('TEST_DPDUK_USERNAME')) {
            DpdUkServiceProvider::enableModule();

            $testAddress = $this->createTestOrder();
            $this->createOrderWithWrongPostCode();
            $this->createOrderWithTooLongStreetAddress();

            /** @var Connection $connection */
            $connection = Connection::factory()->make();
            $connection->collectionAddress()->associate($testAddress);
            $connection->save();
        }
    }

    private function createTestOrder(): OrderAddress
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'My';
        $testAddress->last_name = 'Contact';
        $testAddress->phone = '0121 500 2500';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'GB';
        $testAddress->postcode = 'B66 1BY';
        $testAddress->address1 = 'Roebuck Lane';
        $testAddress->address2 = 'Smethwick';
        $testAddress->city = 'Birmingham';
        $testAddress->state_code = 'West Midlands';
        $testAddress->save();

        /** @var Order $order */
        $order = Order::factory()->make();
        $order->order_number .= '-DPDUK';
        $order->shippingAddress()->associate($testAddress);
        $order->save();
        OrderProduct::factory()->count(3)->create(['order_id' => $order->getKey()]);

        return $testAddress;
    }

    private function createOrderWithTooLongStreetAddress(): void
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'My';
        $testAddress->last_name = 'Contact';
        $testAddress->phone = '0121 500 2500';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'GB';
        $testAddress->postcode = 'B66 1BY';
        $testAddress->address1 = 'Roebuck Lane blue black red 1b';
        $testAddress->address2 = 'Smethwick';
        $testAddress->city = 'Birmingham';
        $testAddress->state_code = 'West Midlands';
        $testAddress->save();

        /** @var Order $order */
        $order = Order::factory()->make();
        $order->order_number .= '-DPDUK-ADDRESS1-TOO-LONG';
        $order->shippingAddress()->associate($testAddress);
        $order->save();
        OrderProduct::factory()->count(3)->create(['order_id' => $order->getKey()]);
    }

    private function createOrderWithWrongPostCode(): void
    {
        /** @var OrderAddress $testAddress */
        $testAddress = OrderAddress::factory()->make();
        $testAddress->first_name = 'My';
        $testAddress->last_name = 'Contact';
        $testAddress->phone = '0121 500 2500';
        $testAddress->company = 'DPD Group Ltd';
        $testAddress->country_code = 'GB';
        $testAddress->postcode = 'B66';
        $testAddress->address1 = 'Roebuck Lane';
        $testAddress->address2 = 'Smethwick';
        $testAddress->city = 'Birmingham';
        $testAddress->state_code = 'West Midlands';
        $testAddress->save();

        /** @var Order $order */
        $order = Order::factory()->make();
        $order->order_number .= '-DPDUK-WRONG-POSTCODE';
        $order->shippingAddress()->associate($testAddress);
        $order->save();
        OrderProduct::factory()->count(3)->create(['order_id' => $order->getKey()]);
    }
}

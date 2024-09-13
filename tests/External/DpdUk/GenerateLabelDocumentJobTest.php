<?php

namespace Tests\External\DpdUk;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Modules\DpdUk\src\Models\Connection;
use App\Modules\DpdUk\src\Services\DpdUkService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateLabelDocumentJobTest extends TestCase
{
    use RefreshDatabase;

    private string $testSkippingExpiryDate = '01 June 2022';

    /**
     * @throws \Exception
     */
    public function test_print_new_label(): void
    {
        if (Carbon::make($this->testSkippingExpiryDate)->isFuture()) {
            $this->markTestSkipped();
        }

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

        /** @var Connection $connection */
        $connection = Connection::factory()->make();
        $connection->collection_address_id = $testAddress->getKey();
        $connection->save();

        $order = Order::factory()->create();
        $shipment = (new DpdUkService)->ship($order->getKey());

        $this->assertGreaterThan(0, $shipment->count());
    }
}

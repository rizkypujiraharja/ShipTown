<?php

namespace Tests\External\DpdIreland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\PrintOrderController
 */
class PrintDpdLabelControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedDpdTestConfiguration;

    /**
     * @test
     */
    public function storeReturnsOkResponse(): void
    {
        $address = OrderAddress::factory()->create([
            'company' => 'TEST COMPANY',
            'address1' => 'ATHLONE BUISNESS PARK',
            'address2' => 'DUBLIN ROAD',
            'phone' => '12345678901',
            'city' => 'Dublin',
            'state_name' => 'Co. Dublin',
            'postcode' => 'N37KD81',
            'country_code' => 'IRL',
            'country_name' => 'Ireland',
        ]);

        /** @var Order $order */
        $order = Order::factory()->create([
            'shipping_address_id' => $address->getKey(),
        ]);

        OrderProduct::factory()->count(2)->create(['order_id' => $order->getKey()]);

        $user = User::factory()->create([
            'address_label_template' => 'dpd_label',
        ]);

        $response = $this->actingAs($user, 'api')
            ->putJson('api/print/order/'.$order->order_number.'/dpd_label', []);

        $this->assertTrue(
            $response->getStatusCode() === 200,
            $response->json('message') ?? ''
        );

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'Status',
                    'Consignment' => [
                        'RecordID',
                        'TrackingNumber',
                        'LabelImage',
                    ],
                ],
            ],
        ]);
    }
}

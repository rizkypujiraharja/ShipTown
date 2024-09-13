<?php

namespace Tests\External\DpdIreland;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderProduct;
use App\Modules\DpdIreland\Dpd;
use App\Modules\DpdIreland\src\Client;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DpdIntegrationJobTest extends TestCase
{
    use RefreshDatabase;
    use SeedDpdTestConfiguration;

    /**
     * @test
     */
    public function if_env_variables_are_set(): void
    {
        $this->assertNotEmpty(env('TEST_DPD_TOKEN'), 'TEST_DPD_TOKEN is not set');
        $this->assertNotEmpty(env('TEST_DPD_USER'), 'TEST_DPD_USER is not set');
        $this->assertNotEmpty(env('TEST_DPD_PASSWORD'), 'TEST_DPD_PASSWORD is not set');
    }

    /**
     * @test
     */
    public function if_authenticates(): void
    {
        $auth = Client::getCachedAuthorization();
        $this->assertEquals('OK', $auth['authorization_response']['Status']);
    }

    /**
     * @test
     */
    public function if_record_id_matches(): void
    {
        $address = OrderAddress::factory()->create([
            'company' => 'DPD Test',
            'first_name' => 'DPD',
            'last_name' => 'Depot',
            'address1' => 'Athlone Business Park',
            'address2' => 'Dublin Road',
            'phone' => '0861230000',
            'city' => 'Athlone',
            'state_name' => 'Athlone',
            'postcode' => '1234XYZ',
            'country_code' => 'IRL',
        ]);

        $order = Order::factory()->create([
            'shipping_address_id' => $address->getKey(),
        ]);

        OrderProduct::factory()->count(2)->create(['order_id' => $order->getKey()]);

        try {
            Dpd::shipOrder($order);
            $success = true;
        } catch (Exception $e) {
            $success = false;
            Log::error($e->getMessage());
            $this->assertTrue($success, $e->getMessage());
        }

        // we just want no exceptions
        $this->assertTrue($success);
    }
}

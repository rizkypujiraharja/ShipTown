<?php

namespace Tests\External\ScurriAnpost;

use App\Modules\ScurriAnpost\src\Api\Client;
use Exception;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_if_credentials_configured(): void
    {
        $this->assertNotNull(config('scurri.base_uri'), 'Scurri base_uri not configured');
        $this->assertNotNull(config('scurri.company_slug'), 'Scurri company_slug not configured');
        $this->assertNotNull(config('scurri.username'), 'Scurri username not configured');
        $this->assertNotNull(config('scurri.password'), 'Scurri password not configured');
    }

    /**
     * A basic feature test example.
     *
     *
     * @throws Exception
     */
    public function test_successful_get_carriers(): void
    {
        $response = Client::getCarriers();

        $this->assertEquals(200, $response->status(), 'Wrong response status code received');
    }

    /**
     * @throws Exception
     */
    public function test_successful_create_consignment(): void
    {
        $consignment = Client::createMultipleConsignments([
            0 => [
                'order_number' => '8384832',
                'recipient' => [
                    'address' => [
                        'country' => 'IE',
                        'postcode' => 'Y35 DW6E',
                        'city' => 'Wexford',
                        'address2' => 'The Bullring',
                        'address1' => 'Innovation House',
                        'state' => 'Wexford',
                    ],
                    'contact_number' => '072 8848292234',
                    'email_address' => 'john@scurri.com',
                    'company_name' => 'Scurri',
                    'name' => 'John Doe',
                ],
                'packages' => [
                    [
                        'items' => [
                            [
                                'sku' => 'n/a',
                                'quantity' => 1,
                                'name' => 'Shipment',
                            ],
                        ],
                        'length' => 10,
                        'height' => 0.5,
                        'width' => 12.2,
                        'reference' => 'CustomCustomerReference1',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(1, $consignment->success->count());
    }

    /**
     * @throws Exception
     */
    public function test_successful_getPdfLabel(): void
    {
        //        $this->markTestSkipped();

        $consignment = Client::createMultipleConsignments([
            0 => [
                'order_number' => '8384832',
                'recipient' => [
                    'address' => [
                        'country' => 'IE',
                        'postcode' => 'Y35 DW6E',
                        'city' => 'Wexford',
                        'address2' => 'The Bullring',
                        'address1' => 'Innovation House',
                        'state' => 'Wexford',
                    ],
                    'contact_number' => '072 8848292234',
                    'email_address' => 'john@scurri.com',
                    'company_name' => 'Scurri',
                    'name' => 'John Doe',
                ],
                'packages' => [
                    [
                        'items' => [
                            [
                                'sku' => 'n/a',
                                'quantity' => 1,
                                'name' => 'Shipment',
                            ],
                        ],
                        'length' => 10,
                        'height' => 0.5,
                        'width' => 12.2,
                        'reference' => 'CustomCustomerReference1',
                    ],
                ],
            ],
        ]);

        $documents = Client::getDocuments($consignment->success[0]);

        $this->assertNotNull($documents->getLabels());
    }
}

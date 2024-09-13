<?php

namespace Tests\External\DpdIreland;

use App\Modules\DpdIreland\Dpd;
use App\Modules\DpdIreland\src\Client;
use App\Modules\DpdIreland\src\Consignment;
use App\Modules\DpdIreland\src\Exceptions\ConsignmentValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DpdTest extends TestCase
{
    use RefreshDatabase;
    use SeedDpdTestConfiguration;

    /**
     * @test
     */
    public function if_env_variables_are_set(): void
    {
        $this->assertNotEmpty(config('dpd.test_token'), 'TEST_DPD_TOKEN is not set');
        $this->assertNotEmpty(config('dpd.test_user'), 'TEST_DPD_USER is not set');
        $this->assertNotEmpty(config('dpd.test_password'), 'TEST_DPD_PASSWORD is not set');
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
     *
     * @throws ConsignmentValidationException
     */
    public function if_record_id_matches(): void
    {
        $consignment = new Consignment([
            'DeliveryAddress' => [
                'Contact' => 'John Smith',
                'ContactTelephone' => '12345678901',
                'ContactEmail' => 'john.smith@ie.ie',
                'BusinessName' => 'JS Business',
                'AddressLine1' => 'DPD Ireland, Westmeath',
                'AddressLine2' => 'Unit 2B Midland Gateway Bus',
                'AddressLine3' => 'Kilbeggan',
                'AddressLine4' => 'Westmeath',
                'CountryCode' => 'IE',
            ],
            'CollectionAddress' => [
                'Contact' => 'John Smith',
                'ContactTelephone' => '12345678901',
                'ContactEmail' => 'john.smith@ie.ie',
                'BusinessName' => 'JS Business',
                'AddressLine1' => 'DPD Ireland, Westmeath',
                'AddressLine2' => 'Unit 2B Midland Gateway Bus',
                'AddressLine3' => 'Kilbeggan',
                'AddressLine4' => 'Westmeath',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertEquals($consignment->toArray()['RecordID'], $preAdvice->getAttribute('RecordID'));
    }

    /**
     * @test
     */
    public function if_authorization_is_cached(): void
    {
        $auth1 = Client::getCachedAuthorization();
        $auth2 = Client::getCachedAuthorization();

        $this->assertTrue($auth2['from_cache']);
        $this->assertEquals($auth1['authorization_response']['AccessToken'], $auth2['authorization_response']['AccessToken']);
    }

    /**
     * @test
     */
    public function successfully_generate_preadvice(): void
    {
        $consignment = new Consignment([
            'RecordID' => 1,
            'TotalParcels' => 1,
            'ServiceOption' => 5,
            'ServiceType' => 1,
            'DeliveryAddress' => [
                'Contact' => 'John Smith',
                'ContactTelephone' => '12345678901',
                'ContactEmail' => 'john.smith@ie.ie',
                'BusinessName' => 'JS Business',
                'AddressLine1' => 'DPD Ireland, Westmeath',
                'AddressLine2' => 'Unit 2B Midland Gateway Bus',
                'AddressLine3' => 'Kilbeggan',
                'AddressLine4' => 'Westmeath',
                'CountryCode' => 'IE',
            ],
            'CollectionAddress' => [
                'Contact' => 'John Smith',
                'ContactTelephone' => '12345678901',
                'ContactEmail' => 'john.smith@ie.ie',
                'BusinessName' => 'JS Business',
                'AddressLine1' => 'DPD Ireland, Westmeath',
                'AddressLine2' => 'Unit 2B Midland Gateway Bus',
                'AddressLine3' => 'Kilbeggan',
                'AddressLine4' => 'Westmeath',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertTrue($preAdvice->isSuccess());
    }
}

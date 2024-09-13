<?php

namespace Tests\External\DpdIreland;

use App\Modules\DpdIreland\Dpd;
use App\Modules\DpdIreland\src\Consignment;
use App\Modules\DpdIreland\src\Exceptions\AuthorizationException;
use App\Modules\DpdIreland\src\Exceptions\ConsignmentValidationException;
use App\Modules\DpdIreland\src\Exceptions\PreAdviceRequestException;
use GuzzleHttp\Exception\GuzzleException;
use Tests\TestCase;

class NormalOvernightTest extends TestCase
{
    use SeedDpdTestConfiguration;

    /**
     * @test
     *
     * @throws AuthorizationException
     * @throws ConsignmentValidationException
     * @throws GuzzleException
     * @throws PreAdviceRequestException
     */
    public function normal_overnight_consignment_single_parcel(): void
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
                'PostCode' => 'XYZ1234',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertEquals($consignment->toArray()['RecordID'], $preAdvice->getAttribute('RecordID'));
    }

    /**
     * @test
     *
     * @throws AuthorizationException
     * @throws ConsignmentValidationException
     * @throws GuzzleException
     * @throws PreAdviceRequestException
     */
    public function normal_overnight_consignment_between_2_and_10_parcels(): void
    {
        $consignment = new Consignment([
            'TotalParcels' => rand(2, 10),
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
                'PostCode' => 'XYZ1234',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertEquals($consignment->toArray()['RecordID'], $preAdvice->getAttribute('RecordID'));
    }

    /**
     * @test
     *
     * @throws AuthorizationException
     * @throws ConsignmentValidationException
     * @throws GuzzleException
     * @throws PreAdviceRequestException
     */
    public function normal_overnight_consignment_more_than_10_parcels(): void
    {
        $consignment = new Consignment([
            'TotalParcels' => rand(11, 20),
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
                'PostCode' => 'XYZ1234',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertEquals($consignment->toArray()['RecordID'], $preAdvice->getAttribute('RecordID'));
    }

    /**
     * @test
     *
     * @throws ConsignmentValidationException
     */
    public function test_if_removes_incorrect_postcode(): void
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
                'PostCode' => 'wrong_postcode_format', // should be 7 characters like 1234XYZ
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

        $this->assertEmpty(data_get($consignment->toArray(), 'DeliveryAddress.PostCode'));
    }

    /**
     * @test
     *
     * @throws ConsignmentValidationException
     */
    public function test_if_preserves_correct_postcode(): void
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
                'PostCode' => '1234XYZ',
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

        $this->assertEquals('1234XYZ', data_get($consignment->toArray(), 'DeliveryAddress.PostCode'));
    }

    /**
     * @test
     *
     * @throws ConsignmentValidationException
     * @throws AuthorizationException
     * @throws PreAdviceRequestException
     * @throws GuzzleException
     */
    public function if_succeeds_with_wrong_postcode(): void
    {
        $consignment = new Consignment([
            'TotalParcels' => rand(11, 20),
            'DeliveryAddress' => [
                'Contact' => 'John Smith',
                'ContactTelephone' => '12345678901',
                'ContactEmail' => 'john.smith@ie.ie',
                'BusinessName' => 'JS Business',
                'AddressLine1' => 'DPD Ireland, Westmeath',
                'AddressLine2' => 'Unit 2B Midland Gateway Bus',
                'AddressLine3' => 'Kilbeggan',
                'AddressLine4' => 'Westmeath',
                'PostCode' => 'XYZ12345678901234',
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
                'PostCode' => 'XYZ1234',
                'CountryCode' => 'IE',
            ],
        ]);

        $preAdvice = Dpd::requestPreAdvice($consignment);

        $this->assertEquals($consignment->toArray()['RecordID'], $preAdvice->getAttribute('RecordID'));
    }
}

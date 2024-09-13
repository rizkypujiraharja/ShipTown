<?php

namespace App\Modules\DpdIreland\src;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Shipment.
 */
class DpdAddress
{
    private Collection $address;

    /**
     * Shipment constructor.
     */
    public function __construct(array $address)
    {
        $this->address = collect($address);
    }

    public function toArray(): array
    {
        return [
            'Contact' => $this->address->get('Contact', ''),
            'ContactTelephone' => $this->address->get('ContactTelephone', ''),
            'ContactEmail' => $this->address->get('ContactEmail', ''),
            'BusinessName' => $this->address->get('BusinessName', ''),
            'AddressLine1' => $this->address->get('AddressLine1', ''),
            'AddressLine2' => $this->address->get('AddressLine2', ''),
            'AddressLine3' => $this->address->get('AddressLine3', ''),
            'AddressLine4' => $this->address->get('AddressLine4', ''),
            'PostCode' => $this->getOnlyCorrectPostCode(),
            'CountryCode' => $this->address->get('CountryCode', ''),
        ];
    }

    public function getOnlyCorrectPostCode(): string
    {
        $countryCode = $this->address->get('CountryCode', '');
        $postCode = $this->address->get('PostCode', '');

        if (in_array($countryCode, ['IE', 'IRL'])) {
            return Str::length($postCode) != 7 ? '' : $postCode;
        }

        return $postCode;
    }
}

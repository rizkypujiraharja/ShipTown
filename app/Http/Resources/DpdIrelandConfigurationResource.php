<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DpdIrelandConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'api_username' => $this->user,
            'api_password' => '*****',
            'api_token' => '*****',
            'api_live' => $this->live,
            'collection_contact' => $this->contact,
            'collection_telephone' => $this->contact_telephone,
            'collection_email' => $this->contact_email,
            'collection_business_name' => $this->business_name,
            'collection_address_line_1' => $this->address_line_1,
            'collection_address_line_2' => $this->address_line_2,
            'collection_address_line_3' => $this->address_line_3,
            'collection_address_line_4' => $this->address_line_4,
            'collection_country_code' => $this->country_code,
        ];
    }
}

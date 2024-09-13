<?php

namespace App\Http\Resources;

use App\Models\OrderAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderAddress
 */
class OrderAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'company' => $this->company,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'email' => $this->email,
            'fax' => $this->fax,
            'first_name' => $this->first_name,
            'gender' => $this->gender,
            'id' => $this->id,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'postcode' => $this->postcode,
            'region' => $this->region,
            'state_code' => $this->state_code,
            'state_name' => $this->state_name,
            'updated_at' => $this->updated_at,
            'website' => $this->website,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

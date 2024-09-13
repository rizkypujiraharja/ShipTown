<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RmsapiConnectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'url' => $this->url,
            'location_id' => $this->location_id,
            'username' => $this->username,
            // Do not send password, even if it's encrypted
        ];
    }
}

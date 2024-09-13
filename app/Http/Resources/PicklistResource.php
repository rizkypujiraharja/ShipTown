<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PicklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'location_id' => $this->location_id,
            'shelve_location' => $this->shelve_location,
            'quantity_requested' => $this->quantity_requested,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'picked_at' => $this->picked_at,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

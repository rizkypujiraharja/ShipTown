<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'carrier' => $this->carrier,
            'service' => $this->service,
            'shipping_number' => $this->shipping_number,
            'tracking_url' => $this->tracking_url,
        ];
    }
}

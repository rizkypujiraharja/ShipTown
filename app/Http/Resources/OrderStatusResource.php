<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'code' => $this->code,
            'order_active' => $this->order_active,
            'order_on_hold' => $this->order_on_hold,
            'hidden' => $this->hidden,
            'sync_ecommerce' => $this->sync_ecommerce,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'order_id' => $this->order_id,
            'order_product_id' => $this->order_product_id,
            'quantity_shipped' => $this->quantity_shipped,
            'order_product_shipment_id' => $this->order_product_shipment_id,
            'order' => OrderResource::make($this->whenLoaded('order')),
            'order_product' => OrderProductResource::make($this->whenLoaded('orderProduct')),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

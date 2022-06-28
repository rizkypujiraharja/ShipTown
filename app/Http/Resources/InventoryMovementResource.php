<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "inventory_id" => $this->inventory_id,
            "product_id" => $this->product_id,
            "warehouse_id" => $this->warehouse_id,
            "quantity_delta" => $this->quantity_delta,
            "quantity_before" => $this->quantity_before,
            "quantity_after" => $this->quantity_after,
            "description" => $this->description,
            "user_id" => $this->user_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,

            "inventory" => InventoryResource::make($this->whenLoaded('inventory')),
            "product" => ProductResource::make($this->whenLoaded('product')),
            "warehouse" => WarehouseResource::make($this->whenLoaded('warehouse')),
            "user" => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
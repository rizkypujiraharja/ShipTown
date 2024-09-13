<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\InventoryGroups\src\Models\InventoryGroup */
class InventoryGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'group_name' => $this->group_name,
            'total_quantity_in_stock' => $this->total_quantity_in_stock,
            'total_quantity_reserved' => $this->total_quantity_reserved,
            'total_quantity_available' => $this->total_quantity_available,
            'total_quantity_incoming' => $this->total_quantity_incoming,
            'total_quantity_required' => $this->total_quantity_required,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPicklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'name_ordered' => $this->name_ordered,
            'sku_ordered' => $this->sku_ordered,
            'total_quantity_to_pick' => (float) $this->total_quantity_to_pick,
            'inventory_source_shelf_location' => $this->inventory_source_shelf_location,
            'inventory_source_quantity' => (float) $this->inventory_source_quantity,
            'order_product_ids' => explode(',', $this->order_product_ids),
            'order_ids' => explode(',', $this->order_ids),
            'product' => new ProductResource($this->whenLoaded('product')),

            // to remove
            'quantity_required' => (float) $this->total_quantity_to_pick,
        ];
    }
}

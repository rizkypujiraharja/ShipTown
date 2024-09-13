<?php

namespace App\Http\Resources;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inventory
 */
class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_code' => $this->warehouse_code,
            'shelf_location' => $this->shelve_location,
            'recount_required' => $this->recount_required,
            'quantity' => $this->quantity,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_available' => $this->quantity_available,
            'quantity_incoming' => $this->quantity_incoming,
            'restock_level' => $this->restock_level,
            'reorder_point' => $this->reorder_point,
            'quantity_required' => $this->quantity_required,
            'last_movement_at' => $this->last_movement_at,
            'first_received_at' => $this->first_received_at,
            'last_received_at' => $this->last_received_at,
            'first_sold_at' => $this->first_sold_at,
            'last_sold_at' => $this->last_sold_at,
            'last_counted_at' => $this->last_counted_at,
            'in_stock_since' => $this->in_stock_since,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}

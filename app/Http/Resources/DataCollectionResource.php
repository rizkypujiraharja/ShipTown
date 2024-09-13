<?php

namespace App\Http\Resources;

use App\Models\DataCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *  Class DataCollectionResource
 *
 * @mixin DataCollection
 */
class DataCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'warehouse_code' => $this->warehouse_code,
            'warehouse_id' => $this->warehouse_id,
            'destination_warehouse_id' => $this->destination_warehouse_id,
            'recount_required' => $this->recount_required,
            'calculated_at' => $this->calculated_at,
            'currently_running_task' => $this->currently_running_task,

            'shipping_address_id' => $this->shipping_address_id,
            'billing_address_id' => $this->billing_address_id,

            'total_quantity_scanned' => $this->total_quantity_scanned,
            'total_cost' => $this->total_cost,
            'total_full_price' => $this->total_full_price,
            'total_discount' => $this->total_discount,
            'total_sold_price' => $this->total_sold_price,
            'total_profit' => $this->total_profit,

            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'shipping_address' => OrderAddressResource::make($this->whenLoaded('shippingAddress')),
            'billing_address' => OrderAddressResource::make($this->whenLoaded('billingAddress')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
        ];
    }
}

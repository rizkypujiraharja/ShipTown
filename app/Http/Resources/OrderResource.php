<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'label_template' => $this->label_template,
            'is_active' => $this->is_active,
            'is_editing' => $this->is_editing,
            'is_fully_paid' => $this->is_fully_paid,
            'total_products' => $this->total_products,
            'total_shipping' => $this->total_shipping,
            'total_discounts' => $this->total_discounts,
            'total_order' => $this->total_order,
            'total_paid' => $this->total_paid,
            'total_outstanding' => $this->total_outstanding,
            'shipping_address_id' => $this->shipping_address_id,
            'billing_address_id' => $this->billing_address_id,
            'shipping_method_code' => $this->shipping_method_code,
            'shipping_method_name' => $this->shipping_method_name,
            'status_code' => $this->status_code,
            'packer_user_id' => $this->packer_user_id,
            'order_placed_at' => $this->order_placed_at,
            'order_closed_at' => $this->order_closed_at,
            'picked_at' => $this->picked_at,
            'packed_at' => $this->packed_at,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order_id' => $this->id,
            'min_shelf_location' => $this->min_shelf_location,
            'max_shelf_location' => $this->max_shelf_location,
            'is_picked' => $this->is_picked,
            'is_packed' => $this->is_packed,
            'age_in_days' => $this->age_in_days,

            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
            'shipping_address' => OrderAddressResource::make($this->whenLoaded('shippingAddress')),
            'billing_address' => OrderAddressResource::make($this->whenLoaded('billingAddress')),
            'order_shipments' => new JsonResource($this->whenLoaded('orderShipments')),
            'order_products' => new JsonResource($this->whenLoaded('orderProducts')),
            'packer' => new UserResource($this->whenLoaded('packer')),
            'order_comments' => new JsonResource($this->whenLoaded('orderComments')),
            'order_products_totals' => new OrderProductsTotalsResource($this->whenLoaded('orderProductsTotals')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}

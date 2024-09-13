<?php

namespace App\Http\Resources;

use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductPrice
 */
class ProductPriceResource extends JsonResource
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
            'is_on_sale' => $this->is_on_sale,
            'current_price' => $this->current_price,
            'price' => $this->price,
            'cost' => $this->cost,
            'sale_price' => $this->sale_price,
            'sale_price_end_date' => $this->sale_price_end_date,
            'sale_price_start_date' => $this->sale_price_start_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

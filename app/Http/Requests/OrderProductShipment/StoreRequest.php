<?php

namespace App\Http\Requests\OrderProductShipment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sku_shipped' => ['sometimes', 'string'],
            'product_id' => ['sometimes', 'integer', 'exists:products,id', 'nullable'],
            'order_id' => [
                'sometimes', 'integer',
                'exists:orders,id',
                //                    Rule::exists('orders', 'id')->where(function ($query) {
                //                        return $query->where('is_active', true);
                //                    }),
            ],
            'order_product_id' => ['sometimes', 'integer', 'exists:orders_products,id'],
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'quantity_shipped' => ['sometimes', 'numeric'],
            'order_shipment_id' => ['sometimes', 'integer', 'exists:orders_products_shipments,id'],
        ];
    }
}

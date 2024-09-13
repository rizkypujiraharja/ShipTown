<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'order_number' => 'required|string',
            'label_template' => 'sometimes|exists:shipping_services,code',
            'products' => 'required|array',
            'products.*.sku' => 'required|string',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|numeric',
            'products.*.price' => 'required|numeric',
        ];
    }
}

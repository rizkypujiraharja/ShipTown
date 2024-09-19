<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'sku' => 'string|required|max:50',
            'name' => 'string|required|max:100',
            'price' => 'required|numeric',
        ];
    }
}

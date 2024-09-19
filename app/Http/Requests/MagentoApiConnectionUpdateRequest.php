<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'base_url' => 'required|url',
            'magento_store_id' => 'required|numeric',
            'tag' => 'required',
            'pricing_source_warehouse_id' => 'required|exists:warehouses,id',
            'api_access_token' => 'required',
        ];
    }
}

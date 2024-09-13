<?php

namespace App\Modules\MagentoApi\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagentoApiConnectionStoreRequest extends FormRequest
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
            'base_url' => 'required|url',
            'magento_store_id' => 'required|numeric',
            'tag' => 'nullable',
            'pricing_source_warehouse_id' => 'nullable|exists:warehouses,id',
            'api_access_token' => 'required',
        ];
    }
}

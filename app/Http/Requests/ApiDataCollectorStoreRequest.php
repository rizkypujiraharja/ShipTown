<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiDataCollectorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'warehouse_code' => ['required', 'exists:warehouses,code'],
            'name' => ['required', 'string'],
            'custom_uuid' => ['nullable', 'string', 'unique:data_collections,custom_uuid'],
            'type' => ['nullable', 'sometimes', 'string', 'in:App\\Models\\DataCollectionTransferIn,App\\Models\\DataCollectionTransferOut,App\\Models\\DataCollectionStocktake,App\\Models\\DataCollectionTransaction'],
            'destination_warehouse_id' => ['nullable', 'sometimes', 'integer', 'exists:warehouses,id'],
        ];
    }
}

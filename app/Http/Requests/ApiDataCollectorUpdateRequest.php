<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiDataCollectorUpdateRequest extends FormRequest
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
            'destination_warehouse_id' => 'sometimes|integer|exists:warehouses,id|required_if:action,transfer_to_scanned',
            'action' => [
                'sometimes',
                'string',
                'in:transfer_in_scanned,transfer_out_scanned,transfer_to_scanned,auto_scan_all_requested,transfer_between_warehouses,import_as_stocktake',
            ],
            'custom_uuid' => 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StocktakesStoreRequest extends FormRequest
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
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'new_quantity' => ['required', 'numeric', 'gte:0'],
        ];
    }
}

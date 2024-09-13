<?php

namespace App\Http\Requests\Picklist;

use Illuminate\Foundation\Http\FormRequest;

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
            'quantity_picked' => ['required', 'numeric'],
            'is_picked' => ['required', 'boolean'],
        ];
    }
}

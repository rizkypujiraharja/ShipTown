<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserMeStoreRequest extends FormRequest
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
            'name' => ['sometimes', 'string'],
            'printer_id' => ['sometimes', 'numeric'],
            'address_label_template' => ['sometimes', 'nullable', 'string', 'exists:"shipping_services",code'],
            'ask_for_shipping_number' => ['sometimes', 'boolean'],
        ];
    }
}

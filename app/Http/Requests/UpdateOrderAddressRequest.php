<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderAddressRequest extends FormRequest
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
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'address1' => 'required|string|max:255',
            'address2' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country_code' => 'required|string|size:2',
            'country_name' => 'required|string|max:255',
            'fax' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'state_code' => 'required|string|max:255',
            'state_name' => 'required|string|max:255',
            'website' => 'required|url|max:255',
        ];
    }
}

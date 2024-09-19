<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderAddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'gender' => 'string|required',
            'address1' => 'string|required',
            'address2' => 'string|required',
            'postcode' => 'string|required',
            'city' => 'string|required',
            'country_code' => 'string|required',
            'country_name' => 'string|required',
            'company' => 'string|nullable',
            'email' => 'email|required',
            'phone' => 'string|required',
        ];
    }
}

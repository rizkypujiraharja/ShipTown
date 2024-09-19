<?php

namespace App\Modules\DpdIreland\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DpdIrelandStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'user' => 'required|string',
            'token' => 'required|string',
            'password' => 'required|string',
            'live' => 'required|boolean',
            'contact' => 'required|string',
            'contact_telephone' => 'required|string',
            'contact_email' => 'sometimes|string',
            'business_name' => 'sometimes|string',
            'address_line_1' => 'sometimes|string',
            'address_line_2' => 'sometimes|string',
            'address_line_3' => 'required|string',
            'address_line_4' => 'required|string',
            'country_code' => 'required|in:IE,IRL,UK,GB',
        ];
    }
}

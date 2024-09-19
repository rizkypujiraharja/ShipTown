<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Magento2MsiConnectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}

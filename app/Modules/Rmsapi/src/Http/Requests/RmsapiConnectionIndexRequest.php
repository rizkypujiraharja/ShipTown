<?php

namespace App\Modules\Rmsapi\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RmsapiConnectionIndexRequest extends FormRequest
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

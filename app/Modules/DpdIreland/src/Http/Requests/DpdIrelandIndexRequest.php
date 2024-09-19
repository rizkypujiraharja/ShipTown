<?php

namespace App\Modules\DpdIreland\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DpdIrelandIndexRequest extends FormRequest
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

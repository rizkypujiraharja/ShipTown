<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintNodeClientDestroyRequest extends FormRequest
{
    public function authorize():bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
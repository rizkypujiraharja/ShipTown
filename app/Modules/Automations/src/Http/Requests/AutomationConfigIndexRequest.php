<?php

namespace App\Modules\Automations\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutomationConfigIndexRequest extends FormRequest
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

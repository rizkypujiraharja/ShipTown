<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoStatusConfigurationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'max_batch_size' => 'sometimes|integer',
            'max_order_age' => 'sometimes|integer',
        ];
    }
}

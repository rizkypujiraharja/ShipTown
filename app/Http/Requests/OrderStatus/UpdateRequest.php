<?php

namespace App\Http\Requests\OrderStatus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'order_active' => ['sometimes', 'boolean'],
            'order_on_hold' => ['sometimes', 'boolean'],
            'hidden' => ['sometimes', 'boolean'],
            'sync_ecommerce' => ['sometimes', 'boolean'],
        ];
    }
}

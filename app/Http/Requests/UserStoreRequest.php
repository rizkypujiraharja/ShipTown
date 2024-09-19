<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string',
                'max:255',
                Rule::unique('users', 'name')->whereNull('deleted_at'),
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            'warehouse_id' => [
                'nullable',
                'exists:warehouses,id',
            ],

            'warehouse_code' => [
                'nullable',
                'exists:warehouses,code',
            ],

            'default_dashboard_uri' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

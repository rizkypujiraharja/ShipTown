<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserUpdateRequest
 *
 * @mixin User
 */
class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $updatedUserId = $this->route('user');

        return [
            'name' => [
                'string',
                'sometimes',
                'required',
                'max:255',
            ],

            'default_dashboard_uri' => [
                'nullable',
                'string',
                'sometimes',
                'max:255',
            ],

            'role_id' => Rule::when($updatedUserId !== $this->user()->id, [
                'sometimes',
                'integer',
                Rule::exists('roles', 'id'),
            ]),

            'printer_id' => [
                'sometimes',
                'numeric',
            ],

            'warehouse_id' => [
                'nullable',
                'exists:warehouses,id',
            ],

            'warehouse_code' => [
                'nullable',
                'exists:warehouses,code',
            ],
        ];
    }
}

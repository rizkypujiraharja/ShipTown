<?php

namespace App\Modules\Rmsapi\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RmsapiConnectionStoreRequest extends FormRequest
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
        return [
            'location_id' => ['required_if:id,null'],
            'url' => 'required_if:id,null|url',
            'username' => 'required_if:id,null',
            'password' => 'required_if:id,null',
            'warehouse_code' => 'sometimes|max:5|exists:warehouse,code',
        ];
    }
}

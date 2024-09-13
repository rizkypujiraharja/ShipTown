<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataCollectionCommentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'data_collection_id' => ['required', 'numeric', 'exists:data_collections,id'],
            'comment' => ['required', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivityStoreRequest extends FormRequest
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
        $rules = [
            'log_name' => ['string', 'max:255'],
            'description' => ['required', 'string'],
            'properties' => ['sometimes', 'array'],

            'subject_type' => ['string', 'in:order'],
            'subject_id' => ['required_with:subject_type', 'integer'],
        ];

        if ($this->has('subject_type')) {
            $modelClass = 'App\\Models\\'.Str::ucfirst($this->get('subject_type'));
            $model = app($modelClass)->findOrFail($this->get('subject_id'));

            $rules['subject_id'][] = Rule::exists($model->getTable(), $model->getKeyName());
        }

        return $rules;
    }
}

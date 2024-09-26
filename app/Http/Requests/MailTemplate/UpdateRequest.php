<?php

namespace App\Http\Requests\MailTemplate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:300'],
            'html_template' => ['required', 'string'],
            'text_template' => ['nullable', 'string'],
            'reply_to' => ['nullable', 'email'],
            'to.*' => ['email'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $email = str_replace(' ', ',', $this->to);
        $email = str_replace(',,', ',', $email);
        $email = collect(explode(',', $email))->filter(function ($email) {
            return $email != '';
        })->toArray();

        $this->merge([
            'to' => $email,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('to.*')) {
                $validator->errors()->add('to', 'The to must be a valid email address.');
            }
        });
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_code' => ['required', 'string', 'exists:mail_templates,code'],
            'data_collection_id' => ['required', 'integer', 'exists:data_collections,id'],
            'output_format' => ['sometimes', 'string', 'in:pdf,txt,html'],
        ];
    }
}

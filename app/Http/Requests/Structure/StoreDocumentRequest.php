<?php

declare(strict_types=1);

namespace App\Http\Requests\Structure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('documents.create');
    }

    public function rules(): array
    {
        return [
            'title'     => ['required', 'string', 'max:150'],
            'category'  => ['required', Rule::in(['legal', 'financial', 'noc', 'circular', 'agreement', 'other'])],
            'file'      => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}

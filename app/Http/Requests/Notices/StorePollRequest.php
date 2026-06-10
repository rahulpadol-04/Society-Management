<?php

declare(strict_types=1);

namespace App\Http\Requests\Notices;

use Illuminate\Foundation\Http\FormRequest;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('notices.create');
    }

    public function rules(): array
    {
        return [
            'question'        => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'multiple_choice' => ['nullable', 'boolean'],
            'closes_at'       => ['nullable', 'date'],
            'options'         => ['required', 'array', 'min:2'],
            'options.*'       => ['required', 'string', 'max:255'],
        ];
    }
}

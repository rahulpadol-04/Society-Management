<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class GenerateBillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('maintenance.generate');
    }

    public function rules(): array
    {
        return [
            'period'   => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'flat_ids' => ['nullable', 'array'],
            'flat_ids.*' => ['integer', 'exists:flats,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'period.regex' => 'Period must be in YYYY-MM format (e.g. 2026-06).',
        ];
    }
}

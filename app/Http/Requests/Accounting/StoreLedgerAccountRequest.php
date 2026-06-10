<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreLedgerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounting.create');
    }

    public function rules(): array
    {
        return [
            'code'            => ['nullable', 'string', 'max:20'],
            'name'            => ['required', 'string', 'max:180'],
            'type'            => ['required', 'in:asset,liability,equity,income,expense'],
            'subtype'         => ['nullable', 'string', 'max:60'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'description'     => ['nullable', 'string'],
        ];
    }
}

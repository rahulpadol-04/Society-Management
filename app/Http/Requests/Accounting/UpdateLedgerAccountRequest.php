<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLedgerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounting.update');
    }

    public function rules(): array
    {
        return [
            'code'            => ['nullable', 'string', 'max:20'],
            'name'            => ['sometimes', 'required', 'string', 'max:180'],
            'type'            => ['sometimes', 'in:asset,liability,equity,income,expense'],
            'subtype'         => ['nullable', 'string', 'max:60'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'description'     => ['nullable', 'string'],
        ];
    }
}

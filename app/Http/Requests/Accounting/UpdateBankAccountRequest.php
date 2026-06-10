<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounting.update');
    }

    public function rules(): array
    {
        return [
            'ledger_account_id' => ['nullable', 'integer', 'exists:ledger_accounts,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:180'],
            'account_type'      => ['sometimes', 'in:bank,cash'],
            'bank_name'         => ['nullable', 'string', 'max:100'],
            'account_number'    => ['nullable', 'string', 'max:30'],
            'ifsc'              => ['nullable', 'string', 'max:15'],
            'opening_balance'   => ['nullable', 'numeric', 'min:0'],
            'current_balance'   => ['nullable', 'numeric'],
            'is_active'         => ['boolean'],
        ];
    }
}

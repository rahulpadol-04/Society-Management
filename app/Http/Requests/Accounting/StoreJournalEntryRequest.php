<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounting.create');
    }

    public function rules(): array
    {
        return [
            'entry_date'                    => ['required', 'date'],
            'narration'                     => ['nullable', 'string', 'max:500'],
            'type'                          => ['nullable', 'in:journal,income,expense,transfer,opening'],
            'status'                        => ['nullable', 'in:draft,posted'],
            'source'                        => ['nullable', 'string', 'max:60'],
            'lines'                         => ['required', 'array', 'min:2'],
            'lines.*.ledger_account_id'     => ['required', 'integer', 'exists:ledger_accounts,id'],
            'lines.*.debit'                 => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit'                => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo'                  => ['nullable', 'string', 'max:255'],
        ];
    }
}

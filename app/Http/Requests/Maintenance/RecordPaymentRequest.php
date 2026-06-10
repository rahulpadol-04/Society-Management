<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('maintenance.collect');
    }

    public function rules(): array
    {
        return [
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'in:cash,cheque,online,upi,card,bank_transfer'],
            'reference' => ['nullable', 'string', 'max:180'],
            'paid_at'   => ['nullable', 'date'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ];
    }
}

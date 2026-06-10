<?php

declare(strict_types=1);

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.pay');
    }

    public function rules(): array
    {
        return [
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'method'        => ['required', 'in:cash,cheque,online,upi,bank_transfer'],
            'reference'     => ['nullable', 'string', 'max:100'],
            'paid_at'       => ['nullable', 'date'],
            'work_order_id' => ['nullable', 'integer'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}

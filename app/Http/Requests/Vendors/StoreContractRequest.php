<?php

declare(strict_types=1);

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.create');
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:180'],
            'contract_number' => ['nullable', 'string', 'max:80'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'value'           => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', 'in:active,expired,terminated,draft'],
            'terms'           => ['nullable', 'string'],
        ];
    }
}

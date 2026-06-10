<?php

declare(strict_types=1);

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('maintenance.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:180'],
            'code'           => ['nullable', 'string', 'max:50'],
            'type'           => ['required', 'in:fixed,per_sqft,per_unit,percentage'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'is_taxable'     => ['boolean'],
            'gst_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'frequency'      => ['required', 'in:monthly,quarterly,half_yearly,yearly,one_time'],
            'is_active'      => ['boolean'],
            'description'    => ['nullable', 'string'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.update');
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'required', 'string', 'max:180'],
            'company'        => ['nullable', 'string', 'max:180'],
            'category'       => ['sometimes', 'required', 'in:plumbing,electrical,housekeeping,security,landscaping,elevator,pest_control,general,other'],
            'contact_person' => ['nullable', 'string', 'max:180'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:180'],
            'gstin'          => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string'],
            'status'         => ['nullable', 'in:active,inactive,blacklisted'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}

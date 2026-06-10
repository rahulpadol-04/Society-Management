<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocietyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('societies.update');
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'email'         => ['nullable', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'postal_code'   => ['nullable', 'string', 'max:12'],
            'status'        => ['nullable', 'in:active,suspended,pending'],
            'plan_id'       => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ];
    }
}

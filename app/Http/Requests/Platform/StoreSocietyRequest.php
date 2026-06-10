<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocietyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('societies.create');
    }

    public function rules(): array
    {
        return [
            // Society
            'name'                => ['required', 'string', 'max:200'],
            'email'               => ['required', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'address_line1'       => ['nullable', 'string', 'max:255'],
            'city'                => ['nullable', 'string', 'max:100'],
            'state'               => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'max:100'],
            'postal_code'         => ['nullable', 'string', 'max:12'],
            'plan_id'             => ['nullable', 'integer', 'exists:subscription_plans,id'],
            // Admin user
            'admin_name'          => ['required', 'string', 'max:200'],
            'admin_email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password'      => ['required', 'string', 'min:8'],
        ];
    }
}

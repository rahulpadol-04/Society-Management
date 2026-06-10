<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('plans.create') || $this->user()->can('plans.update');
    }

    public function rules(): array
    {
        $planId = $this->route('plan')?->id;

        return [
            'name'            => ['required', 'string', 'max:120'],
            'slug'            => [
                'required', 'string', 'max:80',
                Rule::unique('subscription_plans', 'slug')->ignore($planId),
            ],
            'billing_cycle'   => ['required', Rule::in(['trial', 'monthly', 'quarterly', 'annual'])],
            'price'           => ['required', 'numeric', 'min:0'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'trial_days'      => ['nullable', 'integer', 'min:0'],
            'max_units'       => ['nullable', 'integer', 'min:1'],
            'max_users'       => ['nullable', 'integer', 'min:1'],
            'max_storage_mb'  => ['nullable', 'integer', 'min:1'],
            'features'        => ['nullable', 'array'],
            'features.*'      => ['string', Rule::in(config('communityos.features'))],
            'is_active'       => ['boolean'],
            'is_featured'     => ['boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
        ];
    }
}

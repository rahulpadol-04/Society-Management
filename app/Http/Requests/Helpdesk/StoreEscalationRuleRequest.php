<?php

declare(strict_types=1);

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class StoreEscalationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('helpdesk.update');
    }

    public function rules(): array
    {
        return [
            'level'       => ['required', 'integer', 'min:1', 'max:10'],
            'name'        => ['required', 'string', 'max:100'],
            'after_hours' => ['required', 'integer', 'min:1'],
            'notify_role' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('helpdesk.create');
    }

    public function rules(): array
    {
        return [
            'subject'     => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category'    => ['required', 'in:general,technical,billing,facility,security,account,other'],
            'priority'    => ['required', 'in:low,medium,high,urgent'],
        ];
    }
}

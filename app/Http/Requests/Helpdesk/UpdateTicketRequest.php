<?php

declare(strict_types=1);

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('helpdesk.update');
    }

    public function rules(): array
    {
        return [
            'subject'     => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category'    => ['sometimes', 'in:general,technical,billing,facility,security,account,other'],
            'priority'    => ['sometimes', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'status'      => ['sometimes', 'in:open,in_progress,on_hold,resolved,closed'],
            'note'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('communication.templates');
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:120'],
            'channel'   => ['required', 'string', 'in:email,sms,whatsapp,push,in_app'],
            'subject'   => ['nullable', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];
    }
}

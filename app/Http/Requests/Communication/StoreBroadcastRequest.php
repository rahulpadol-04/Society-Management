<?php

declare(strict_types=1);

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class StoreBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('communication.broadcast');
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:180'],
            'message'      => ['required', 'string'],
            'channels'     => ['required', 'array', 'min:1'],
            'channels.*'   => ['string', 'in:email,sms,whatsapp,push,in_app'],
            'audience'     => ['required', 'string', 'in:all,owners,tenants,staff,residents'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Helpdesk;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('helpdesk.view');
    }

    public function rules(): array
    {
        return [
            'message'     => ['required', 'string', 'max:5000'],
            'is_internal' => ['boolean'],
        ];
    }
}

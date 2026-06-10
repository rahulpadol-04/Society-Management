<?php

declare(strict_types=1);

namespace App\Http\Requests\Notices;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('notices.update');
    }

    public function rules(): array
    {
        return [
            'title'      => ['sometimes', 'required', 'string', 'max:255'],
            'body'       => ['sometimes', 'required', 'string'],
            'category'   => ['sometimes', 'required', 'in:notice,announcement,circular,event'],
            'audience'   => ['sometimes', 'required', 'in:all,owners,tenants,staff'],
            'pinned'     => ['nullable', 'boolean'],
            'event_at'   => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Notices;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('notices.create');
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'body'            => ['required', 'string'],
            'category'        => ['required', 'in:notice,announcement,circular,event'],
            'audience'        => ['required', 'in:all,owners,tenants,staff'],
            'pinned'          => ['nullable', 'boolean'],
            'event_at'        => ['nullable', 'date', 'required_if:category,event'],
            'attachment'      => ['nullable', 'file', 'max:10240'],
            // Optional inline poll
            'poll_question'        => ['nullable', 'string', 'max:255'],
            'poll_description'     => ['nullable', 'string'],
            'poll_multiple_choice' => ['nullable', 'boolean'],
            'poll_closes_at'       => ['nullable', 'date'],
            'poll_options'         => ['nullable', 'array', 'min:2'],
            'poll_options.*'       => ['required_with:poll_options', 'string', 'max:255'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCmsPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cms.create') || $this->user()->can('cms.update');
    }

    public function rules(): array
    {
        $pageId = $this->route('cms_page')?->id;

        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => [
                'required', 'string', 'max:255',
                Rule::unique('cms_pages', 'slug')->ignore($pageId)->whereNull('deleted_at'),
            ],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status'           => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}

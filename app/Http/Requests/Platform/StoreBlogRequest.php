<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blog.create') || $this->user()->can('blog.update');
    }

    public function rules(): array
    {
        $blogId = $this->route('blog')?->id;

        return [
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => [
                'required', 'string', 'max:255',
                Rule::unique('blogs', 'slug')->ignore($blogId)->whereNull('deleted_at'),
            ],
            'excerpt'     => ['nullable', 'string', 'max:1000'],
            'content'     => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:100'],
            'status'      => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}

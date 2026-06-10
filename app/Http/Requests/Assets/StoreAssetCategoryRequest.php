<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assets.create');
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:150'],
            'depreciation_rate'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'useful_life_years'  => ['nullable', 'integer', 'min:1'],
            'is_active'          => ['nullable', 'boolean'],
        ];
    }
}

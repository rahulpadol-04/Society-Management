<?php

declare(strict_types=1);

namespace App\Http\Requests\Structure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('structure.create');
    }

    public function rules(): array
    {
        $towerId = $this->route('tower')?->id;

        return [
            'name'            => ['required', 'string', 'max:120'],
            'code'            => [
                'nullable', 'string', 'max:30',
                Rule::unique('towers', 'code')->where('society_id', current_society_id())->ignore($towerId),
            ],
            'type'            => ['required', Rule::in(['tower', 'block', 'building', 'wing'])],
            'total_floors'    => ['nullable', 'integer', 'min:0', 'max:200'],
            'units_per_floor' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'status'          => ['required', Rule::in(['active', 'inactive'])],
            'scaffold'        => ['sometimes', 'boolean'],
        ];
    }
}

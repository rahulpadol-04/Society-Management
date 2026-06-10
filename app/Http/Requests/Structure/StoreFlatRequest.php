<?php

declare(strict_types=1);

namespace App\Http\Requests\Structure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $flat = $this->route('flat');

        return $flat ? $this->user()->can('structure.update') : $this->user()->can('structure.create');
    }

    public function rules(): array
    {
        $flatId = $this->route('flat')?->id;
        $sid = current_society_id();

        return [
            'tower_id'           => ['required', Rule::exists('towers', 'id')->where('society_id', $sid)],
            'floor_id'           => ['nullable', Rule::exists('floors', 'id')->where('society_id', $sid)],
            'number'             => [
                'required', 'string', 'max:30',
                Rule::unique('flats', 'number')->where('society_id', $sid)->ignore($flatId),
            ],
            'type'               => ['nullable', 'string', 'max:30'],
            'carpet_area'        => ['nullable', 'numeric', 'min:0'],
            'built_up_area'      => ['nullable', 'numeric', 'min:0'],
            'bedrooms'           => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'          => ['nullable', 'integer', 'min:0', 'max:20'],
            'ownership'          => ['required', Rule::in(['owner_occupied', 'rented', 'self', 'company'])],
            'status'             => ['required', Rule::in(['occupied', 'vacant', 'under_construction', 'on_rent'])],
            'owner_id'           => ['nullable', Rule::exists('users', 'id')->where('society_id', $sid)],
            'maintenance_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

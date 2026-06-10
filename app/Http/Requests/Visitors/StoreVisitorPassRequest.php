<?php

declare(strict_types=1);

namespace App\Http\Requests\Visitors;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorPassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('visitors.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'type'           => ['required', 'in:guest,delivery,cab,service,vendor'],
            'purpose'        => ['nullable', 'string', 'max:255'],
            'flat_id'        => ['nullable', 'integer'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'expected_at'    => ['nullable', 'date'],
            'valid_until'    => ['nullable', 'date', 'after_or_equal:expected_at'],
            'max_entries'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Visitors;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('visitors.checkin');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required_without:code', 'nullable', 'string', 'max:150'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'type'           => ['nullable', 'in:guest,delivery,cab,service,vendor'],
            'purpose'        => ['nullable', 'string', 'max:255'],
            'flat_id'        => ['nullable', 'integer'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'gate'           => ['nullable', 'string', 'max:50'],
            'code'           => ['nullable', 'string', 'max:30'],
        ];
    }
}

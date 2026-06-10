<?php

declare(strict_types=1);

namespace App\Http\Requests\Facilities;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('facilities.update');
    }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'required', 'string', 'max:180'],
            'type'              => ['sometimes', 'required', 'in:clubhouse,gym,pool,court,hall,other'],
            'description'       => ['nullable', 'string'],
            'capacity'          => ['nullable', 'integer', 'min:1'],
            'charge'            => ['nullable', 'numeric', 'min:0'],
            'requires_approval' => ['boolean'],
            'opening_time'      => ['nullable', 'date_format:H:i'],
            'closing_time'      => ['nullable', 'date_format:H:i'],
            'slot_minutes'      => ['nullable', 'integer', 'min:15'],
            'is_active'         => ['boolean'],
            'image'             => ['nullable', 'image', 'max:2048'],
        ];
    }
}

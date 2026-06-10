<?php

declare(strict_types=1);

namespace App\Http\Requests\Structure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParkingSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        $slot = $this->route('parking_slot');

        return $slot ? $this->user()->can('parking.update') : $this->user()->can('parking.create');
    }

    public function rules(): array
    {
        $slotId = $this->route('parking_slot')?->id;
        $sid = current_society_id();

        return [
            'code'     => [
                'required', 'string', 'max:30',
                Rule::unique('parking_slots', 'code')->where('society_id', $sid)->ignore($slotId),
            ],
            'type'     => ['required', Rule::in(['car', 'bike', 'visitor', 'ev', 'handicap'])],
            'location' => ['nullable', 'string', 'max:120'],
            'flat_id'  => ['nullable', Rule::exists('flats', 'id')->where('society_id', $sid)],
            'status'   => ['required', Rule::in(['available', 'assigned', 'reserved', 'blocked'])],
        ];
    }
}

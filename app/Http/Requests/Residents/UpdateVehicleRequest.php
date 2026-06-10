<?php

declare(strict_types=1);

namespace App\Http\Requests\Residents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        // Owner may update their own vehicle; otherwise requires the permission.
        if ($vehicle && $vehicle->resident && $vehicle->resident->user_id === $this->user()->id) {
            return true;
        }

        return $this->user()->can('vehicles.update');
    }

    public function rules(): array
    {
        $sid       = current_society_id();
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'type'                => ['sometimes', Rule::in(['car', 'bike', 'other'])],
            'make'                => ['nullable', 'string', 'max:60'],
            'model'               => ['nullable', 'string', 'max:60'],
            'registration_number' => [
                'sometimes', 'required', 'string', 'max:30',
                Rule::unique('vehicles', 'registration_number')
                    ->where('society_id', $sid)
                    ->ignore($vehicleId),
            ],
            'color'               => ['nullable', 'string', 'max:40'],
            'rfid_tag'            => ['nullable', 'string', 'max:60'],
            'flat_id'             => ['nullable', Rule::exists('flats', 'id')->where('society_id', $sid)],
            'resident_id'         => ['nullable', Rule::exists('residents', 'id')->where('society_id', $sid)],
            'parking_slot_id'     => ['nullable', Rule::exists('parking_slots', 'id')->where('society_id', $sid)],
            'status'              => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}

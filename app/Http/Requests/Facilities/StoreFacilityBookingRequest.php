<?php

declare(strict_types=1);

namespace App\Http\Requests\Facilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('facilities.book');
    }

    public function rules(): array
    {
        return [
            'facility_id'  => ['required', 'integer', Rule::exists('facilities', 'id')->where('society_id', current_society_id())],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'guests'       => ['nullable', 'integer', 'min:0'],
            'flat_id'      => ['nullable', 'integer'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}

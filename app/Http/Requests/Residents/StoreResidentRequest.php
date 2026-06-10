<?php

declare(strict_types=1);

namespace App\Http\Requests\Residents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('residents.create');
    }

    public function rules(): array
    {
        $sid = current_society_id();

        return [
            'name'          => ['required', 'string', 'max:120'],
            'type'          => ['required', Rule::in(['owner', 'tenant', 'family_member'])],
            'email'         => ['nullable', 'email', 'max:180'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'flat_id'       => ['nullable', Rule::exists('flats', 'id')->where('society_id', $sid)],
            'user_id'       => ['nullable', Rule::exists('users', 'id')->where('society_id', $sid)],
            'parent_id'     => ['nullable', Rule::exists('residents', 'id')->where('society_id', $sid)],
            'relation'      => ['nullable', 'string', 'max:60'],
            'is_primary'    => ['sometimes', 'boolean'],
            'photo'         => ['nullable', 'image', 'max:2048'],
            'move_in_date'  => ['nullable', 'date'],
            'move_out_date' => ['nullable', 'date', 'after_or_equal:move_in_date'],
            'status'        => ['sometimes', Rule::in(['active', 'inactive', 'moved_out'])],
        ];
    }
}

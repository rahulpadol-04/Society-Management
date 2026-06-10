<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('society-staff.create');
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:191'],
            'employee_code'  => ['nullable', 'string', 'max:50'],
            'designation'    => ['nullable', 'string', 'max:191'],
            'department'     => ['required', Rule::in(['security', 'housekeeping', 'maintenance', 'admin', 'gardening', 'plumbing', 'electrical', 'other'])],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:191'],
            'joining_date'   => ['nullable', 'date'],
            'salary'         => ['nullable', 'numeric', 'min:0'],
            'shift'          => ['nullable', Rule::in(['morning', 'evening', 'night', 'general'])],
            'status'         => ['nullable', Rule::in(['active', 'inactive', 'on_leave', 'terminated'])],
            'address'        => ['nullable', 'string', 'max:500'],
            'photo'          => ['nullable', 'image', 'max:2048'],
        ];
    }
}

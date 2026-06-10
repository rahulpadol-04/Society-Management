<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('society-staff.update');
    }

    public function rules(): array
    {
        return [
            'staff_member_id' => ['required', 'integer', 'exists:staff_members,id'],
            'type'            => ['required', Rule::in(['casual', 'sick', 'paid', 'unpaid'])],
            'from_date'       => ['required', 'date'],
            'to_date'         => ['required', 'date', 'gte:from_date'],
            'days'            => ['nullable', 'integer', 'min:1'],
            'reason'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}

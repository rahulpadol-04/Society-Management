<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assets.schedule');
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:200'],
            'frequency'      => ['required', 'in:weekly,monthly,quarterly,half_yearly,yearly,one_time'],
            'next_due_date'  => ['nullable', 'date'],
            'assigned_to'    => ['nullable', 'integer', 'exists:users,id'],
            'vendor_id'      => ['nullable', 'integer'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:2000'],
        ];
    }
}

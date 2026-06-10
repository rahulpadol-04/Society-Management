<?php

declare(strict_types=1);

namespace App\Http\Requests\Complaints;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complaints.update');
    }

    public function rules(): array
    {
        return [
            'title'                 => ['sometimes', 'required', 'string', 'max:180'],
            'description'           => ['nullable', 'string'],
            'complaint_category_id' => ['nullable', 'integer', 'exists:complaint_categories,id'],
            'priority'              => ['sometimes', 'in:low,medium,high,critical'],
            'assigned_to'           => ['nullable', 'integer', 'exists:users,id'],
            'status'                => ['sometimes', 'in:open,assigned,in_progress,resolved,closed'],
            'note'                  => ['nullable', 'string', 'max:1000'],
        ];
    }
}

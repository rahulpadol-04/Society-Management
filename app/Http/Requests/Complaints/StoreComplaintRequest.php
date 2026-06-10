<?php

declare(strict_types=1);

namespace App\Http\Requests\Complaints;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('complaints.create');
    }

    public function rules(): array
    {
        return [
            'title'                 => ['required', 'string', 'max:180'],
            'description'           => ['nullable', 'string'],
            'complaint_category_id' => ['nullable', 'integer', 'exists:complaint_categories,id'],
            'priority'              => ['required', 'in:low,medium,high,critical'],
            'flat_id'               => ['nullable', 'integer'],
            'attachments'           => ['nullable', 'array'],
            'attachments.*'         => ['file', 'max:5120'],
        ];
    }
}

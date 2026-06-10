<?php

declare(strict_types=1);

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.create');
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:180'],
            'description'    => ['nullable', 'string'],
            'priority'       => ['nullable', 'in:low,medium,high,critical'],
            'status'         => ['nullable', 'in:open,assigned,in_progress,completed,cancelled'],
            'amount'         => ['nullable', 'numeric', 'min:0'],
            'scheduled_for'  => ['nullable', 'date'],
            'complaint_id'   => ['nullable', 'integer'],
        ];
    }
}

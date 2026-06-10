<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assets.schedule');
    }

    public function rules(): array
    {
        return [
            'performed_on'  => ['nullable', 'date'],
            'cost'          => ['nullable', 'numeric', 'min:0'],
            'performed_by'  => ['nullable', 'string', 'max:200'],
            'vendor_id'     => ['nullable', 'integer'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ];
    }
}

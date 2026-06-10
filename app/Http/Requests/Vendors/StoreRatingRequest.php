<?php

declare(strict_types=1);

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.rate');
    }

    public function rules(): array
    {
        return [
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'comment'       => ['nullable', 'string', 'max:1000'],
            'work_order_id' => ['nullable', 'integer'],
        ];
    }
}

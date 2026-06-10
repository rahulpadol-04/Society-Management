<?php

declare(strict_types=1);

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assets.update');
    }

    public function rules(): array
    {
        return [
            'name'                  => ['sometimes', 'required', 'string', 'max:200'],
            'code'                  => ['nullable', 'string', 'max:60'],
            'asset_category_id'     => ['nullable', 'integer', 'exists:asset_categories,id'],
            'description'           => ['nullable', 'string'],
            'location'              => ['nullable', 'string', 'max:200'],
            'tower_id'              => ['nullable', 'integer'],
            'vendor_id'             => ['nullable', 'integer'],
            'purchase_date'         => ['nullable', 'date'],
            'purchase_cost'         => ['nullable', 'numeric', 'min:0'],
            'salvage_value'         => ['nullable', 'numeric', 'min:0'],
            'depreciation_method'   => ['nullable', 'in:straight_line,declining_balance,none'],
            'depreciation_rate'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'useful_life_years'     => ['nullable', 'integer', 'min:1'],
            'status'                => ['nullable', 'in:active,under_maintenance,retired,disposed'],
            'warranty_until'        => ['nullable', 'date'],
            'image'                 => ['nullable', 'image', 'max:2048'],
        ];
    }
}
